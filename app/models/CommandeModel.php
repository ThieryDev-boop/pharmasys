<?php
// app/models/CommandeModel.php

class CommandeModel extends Model {

    // ── LISTE ────────────────────────────────────────────────

    public function findAll(array $filtres = []): array {
        $sql = "SELECT c.*,
                       f.raison_sociale,
                       CONCAT(u.nom,' ',COALESCE(u.prenom,'')) AS createur
                FROM commandes c
                JOIN fournisseurs f ON c.id_fournisseur = f.id_fournisseur
                JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
                WHERE 1=1";
        $params = [];

        if (!empty($filtres['statut'])) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }
        if (!empty($filtres['search'])) {
            $sql .= " AND (c.numero_commande LIKE :s OR f.raison_sociale LIKE :s2)";
            $params[':s']  = '%' . $filtres['search'] . '%';
            $params[':s2'] = '%' . $filtres['search'] . '%';
        }

        $sql .= " ORDER BY c.date_commande DESC";
        return $this->query($sql, $params);
    }

    public function findById(int $id) {
        return $this->queryOne(
            "SELECT c.*,
                    f.raison_sociale, f.telephone, f.email, f.adresse,
                    CONCAT(u.nom,' ',COALESCE(u.prenom,'')) AS createur
             FROM commandes c
             JOIN fournisseurs f ON c.id_fournisseur = f.id_fournisseur
             JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
             WHERE c.id_commande = :id",
            [':id' => $id]
        );
    }

    public function getLignes(int $idCommande): array {
        return $this->query(
            "SELECT l.*,
                    m.nom_commercial, m.dci, m.forme_galenique,
                    m.dosage, m.prix_achat
             FROM lignes_commande l
             JOIN medicaments m ON l.id_medicament = m.id_medicament
             WHERE l.id_commande = :id
             ORDER BY m.nom_commercial ASC",
            [':id' => $idCommande]
        );
    }

    // ── CRÉATION ─────────────────────────────────────────────

    public function creer(int $idFournisseur, int $idUtilisateur, string $note = '', string $dateLivraison = ''): int {
        $numero = $this->genererNumero();
        return (int)$this->insert(
            "INSERT INTO commandes
                (numero_commande, id_fournisseur, id_utilisateur, note, date_livraison_prevue, statut)
             VALUES (:num, :fournisseur, :user, :note, :livraison, 'brouillon')",
            [
                ':num'        => $numero,
                ':fournisseur'=> $idFournisseur,
                ':user'       => $idUtilisateur,
                ':note'       => $note ?: null,
                ':livraison'  => $dateLivraison ?: null,
            ]
        );
    }

    private function genererNumero(): string {
        $annee   = date('Y');
        $dernier = $this->queryOne(
            "SELECT numero_commande FROM commandes
             WHERE numero_commande LIKE :prefix
             ORDER BY id_commande DESC LIMIT 1",
            [':prefix' => "CMD-{$annee}-%"]
        );
        $seq = 1;
        if ($dernier) {
            $parts = explode('-', $dernier['numero_commande']);
            $seq   = (int)end($parts) + 1;
        }
        return sprintf('CMD-%s-%04d', $annee, $seq);
    }

    // ── LIGNES ───────────────────────────────────────────────

    public function ajouterLigne(int $idCommande, int $idMedicament, int $qte, float $prix): int {
        // Si le médicament existe déjà, on cumule la quantité
        $existant = $this->queryOne(
            "SELECT id_ligne_cmd, quantite_commandee FROM lignes_commande
             WHERE id_commande = :c AND id_medicament = :m",
            [':c' => $idCommande, ':m' => $idMedicament]
        );
        if ($existant) {
            $this->execute(
                "UPDATE lignes_commande
                 SET quantite_commandee = quantite_commandee + :q,
                     prix_unitaire = :p
                 WHERE id_ligne_cmd = :id",
                [':q' => $qte, ':p' => $prix, ':id' => $existant['id_ligne_cmd']]
            );
            $this->recalculerTotal($idCommande);
            return (int)$existant['id_ligne_cmd'];
        }

        $id = (int)$this->insert(
            "INSERT INTO lignes_commande
                (id_commande, id_medicament, quantite_commandee, prix_unitaire)
             VALUES (:c, :m, :q, :p)",
            [':c' => $idCommande, ':m' => $idMedicament, ':q' => $qte, ':p' => $prix]
        );
        $this->recalculerTotal($idCommande);
        return $id;
    }

    public function supprimerLigne(int $idLigne, int $idCommande): void {
        $this->execute(
            "DELETE FROM lignes_commande WHERE id_ligne_cmd = :id",
            [':id' => $idLigne]
        );
        $this->recalculerTotal($idCommande);
    }

    public function recalculerTotal(int $idCommande): void {
        $this->execute(
            "UPDATE commandes SET montant_total =
                (SELECT COALESCE(SUM(quantite_commandee * prix_unitaire), 0)
                 FROM lignes_commande WHERE id_commande = :id)
             WHERE id_commande = :id2",
            [':id' => $idCommande, ':id2' => $idCommande]
        );
    }

    // ── CHANGEMENTS DE STATUT ─────────────────────────────────

    public function envoyer(int $id): void {
        $this->execute(
            "UPDATE commandes SET statut = 'envoye' WHERE id_commande = :id AND statut = 'brouillon'",
            [':id' => $id]
        );
    }

    public function annuler(int $id): void {
        $this->execute(
            "UPDATE commandes SET statut = 'annule'
             WHERE id_commande = :id AND statut IN ('brouillon','envoye')",
            [':id' => $id]
        );
    }

    // ── RÉCEPTION ────────────────────────────────────────────

    public function recevoirLigne(int $idLigne, int $qteRecue): void {
        $this->execute(
            "UPDATE lignes_commande
             SET quantite_recue = quantite_recue + :q,
                 statut_ligne = CASE
                     WHEN quantite_recue + :q2 >= quantite_commandee THEN 'recue_totale'
                     ELSE 'recue_partielle'
                 END
             WHERE id_ligne_cmd = :id",
            [':q' => $qteRecue, ':q2' => $qteRecue, ':id' => $idLigne]
        );
    }

    public function mettreAJourStatutCommande(int $idCommande): void {
        $lignes = $this->query(
            "SELECT statut_ligne FROM lignes_commande WHERE id_commande = :id",
            [':id' => $idCommande]
        );
        if (empty($lignes)) return;

        $statuts  = array_column($lignes, 'statut_ligne');
        $toutRecu = !in_array('en_attente', $statuts) && !in_array('recue_partielle', $statuts);
        $unRecu   = in_array('recue_totale', $statuts) || in_array('recue_partielle', $statuts);

        if ($toutRecu) {
            $nouveau = 'recu_totalement';
        } elseif ($unRecu) {
            $nouveau = 'recu_partiellement';
        } else {
            return;
        }

        $this->execute(
            "UPDATE commandes SET statut = :s WHERE id_commande = :id",
            [':s' => $nouveau, ':id' => $idCommande]
        );
    }

    // ── STATS ─────────────────────────────────────────────────

    public function compterParStatut(): array {
        $rows = $this->query(
            "SELECT statut, COUNT(*) AS nb FROM commandes GROUP BY statut"
        );
        $result = [];
        foreach ($rows as $r) $result[$r['statut']] = $r['nb'];
        return $result;
    }

    public function enregistrerReception(int $idCommande, array $receptions): void {
    $config = require CONFIG_PATH . '/database.php';
    $dsn    = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo    = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    $pdo->beginTransaction();
    try {
        foreach ($receptions as $idLigne => $data) {
            $qteRecue = (int)($data['qte'] ?? 0);
            if ($qteRecue <= 0) continue;

            $this->recevoirLigne((int)$idLigne, $qteRecue);

            $numeroLot      = trim($data['numero_lot']      ?? '');
            $datePeremption = trim($data['date_peremption'] ?? '');

            if ($numeroLot && $datePeremption) {
                $stmt = $pdo->prepare(
                    "INSERT INTO lots
                        (id_medicament, numero_lot, date_fabrication, date_peremption,
                         quantite_initiale, quantite_restante, prix_achat, id_commande)
                     VALUES (:med, :lot, :fab, :per, :qi, :qr, :pa, :cmd)"
                );
                $stmt->execute([
                    ':med' => (int)($data['id_medicament'] ?? 0),
                    ':lot' => $numeroLot,
                    ':fab' => trim($data['date_fabrication'] ?? '') ?: null,
                    ':per' => $datePeremption,
                    ':qi'  => $qteRecue,
                    ':qr'  => $qteRecue,
                    ':pa'  => (float)($data['prix_unitaire'] ?? 0),
                    ':cmd' => $idCommande,
                ]);
            }
        }

        $this->mettreAJourStatutCommande($idCommande);
        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}
