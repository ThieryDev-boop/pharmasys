<?php
// app/models/VenteModel.php

class VenteModel extends Model {

    // Creer une vente en brouillon
    public function creer(int $idUtilisateur, $idClient): int {
        $numero = $this->genererNumero();
        return (int) $this->insert(
            "INSERT INTO ventes (numero_facture, id_client, id_utilisateur, statut)
             VALUES (:num, :cli, :usr, 'brouillon')",
            [':num' => $numero, ':cli' => $idClient ?: null, ':usr' => $idUtilisateur]
        );
    }

    // Generer un numero de facture unique
    private function genererNumero(): string {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*)+1 AS n FROM ventes WHERE DATE(date_vente) = CURDATE()"
        );
        $n = (int)$stmt->fetchColumn();
        return 'FAC-' . date('Ymd') . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // Ajouter une ligne de vente
    public function ajouterLigne(array $data): bool {
        return $this->execute(
            "INSERT INTO lignes_vente
             (id_vente, id_medicament, id_lot, quantite, prix_unitaire, remise, montant_ligne)
             VALUES (:v, :m, :l, :q, :pu, :rem, :mt)",
            [
                ':v'   => $data['id_vente'],
                ':m'   => $data['id_medicament'],
                ':l'   => $data['id_lot'],
                ':q'   => $data['quantite'],
                ':pu'  => $data['prix_unitaire'],
                ':rem' => $data['remise'] ?? 0,
                ':mt'  => $data['montant_ligne'],
            ]
        );
    }

    // Supprimer une ligne de vente (et restituer le stock)
    public function supprimerLigne(int $idLigne): array {
        $ligne = $this->queryOne(
            "SELECT * FROM lignes_vente WHERE id_ligne = :id",
            [':id' => $idLigne]
        );
        if ($ligne) {
            $this->execute(
                "DELETE FROM lignes_vente WHERE id_ligne = :id",
                [':id' => $idLigne]
            );
        }
        return $ligne ?: [];
    }

    // Recalculer les totaux de la vente
    public function recalculerTotal(int $idVente): void {
        $this->execute(
            "UPDATE ventes SET
             montant_total_ttc = (
                 SELECT COALESCE(SUM(montant_ligne), 0)
                 FROM lignes_vente WHERE id_vente = :id
             )
             WHERE id_vente = :id2",
            [':id' => $idVente, ':id2' => $idVente]
        );
    }

    // Finaliser la vente
    public function finaliser(int $idVente, float $montantPaye): bool {
        $vente = $this->findById($idVente);
        if (!$vente) return false;

        $monnaie = round($montantPaye - $vente['montant_total_ttc'], 2);

        return $this->execute(
            "UPDATE ventes SET
             statut         = 'validee',
             montant_paye   = :mp,
             monnaie_rendue = :mr
             WHERE id_vente = :id AND statut = 'brouillon'",
            [':mp' => $montantPaye, ':mr' => max(0, $monnaie), ':id' => $idVente]
        );
    }

    // Annuler une vente
    public function annuler(int $idVente, string $motif = ''): bool {
        return $this->execute(
            "UPDATE ventes SET statut = 'annulee', motif_annulation = :m
             WHERE id_vente = :id",
            [':m' => $motif, ':id' => $idVente]
        );
    }

    // Trouver une vente par ID avec details
    public function findById(int $id) {
        return $this->queryOne(
            "SELECT v.*,
                    CONCAT(u.nom,' ',u.prenom) AS caissier_nom,
                    CONCAT(COALESCE(c.nom,''), ' ', COALESCE(c.prenom,'')) AS client_nom,
                    c.telephone AS client_telephone
             FROM ventes v
             JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
             LEFT JOIN clients c ON v.id_client = c.id_client
             WHERE v.id_vente = :id",
            [':id' => $id]
        );
    }

    // Liste des lignes d'une vente
    public function getLignes(int $idVente): array {
        return $this->query(
            "SELECT lv.*, m.nom_commercial, m.dci, m.forme_galenique, l.numero_lot
             FROM lignes_vente lv
             JOIN medicaments m ON lv.id_medicament = m.id_medicament
             JOIN lots l ON lv.id_lot = l.id_lot
             WHERE lv.id_vente = :id",
            [':id' => $idVente]
        );
    }

    // Historique des ventes validees avec pagination
    public function findAllValidees(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT v.*,
                    CONCAT(u.nom,' ',u.prenom) AS caissier_nom,
                    CONCAT(COALESCE(c.nom,''), ' ', COALESCE(c.prenom,'')) AS client_nom
                FROM ventes v
                JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
                LEFT JOIN clients c ON v.id_client = c.id_client
                WHERE v.statut = 'validee'";
        $params = [];
        if ($search) {
            $sql .= " AND (v.numero_facture LIKE :s OR c.nom LIKE :s2)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
        }
        $sql .= " ORDER BY v.date_vente DESC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAllValidees(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM ventes v
                LEFT JOIN clients c ON v.id_client = c.id_client
                WHERE v.statut = 'validee'";
        $params = [];
        if ($search) {
            $sql .= " AND (v.numero_facture LIKE :s OR c.nom LIKE :s2)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Stats pour le dashboard
    public function getStatsDashboard(): array {
        $r = $this->queryOne(
            "SELECT
               COUNT(CASE WHEN DATE(date_vente) = CURDATE() AND statut='validee' THEN 1 END) AS ventes_jour,
               COALESCE(SUM(CASE WHEN DATE(date_vente) = CURDATE() AND statut='validee'
                            THEN montant_total_ttc END), 0) AS ca_jour,
               COALESCE(SUM(CASE WHEN MONTH(date_vente) = MONTH(CURDATE())
                            AND YEAR(date_vente) = YEAR(CURDATE())
                            AND statut='validee'
                            THEN montant_total_ttc END), 0) AS ca_mois
             FROM ventes"
        );
        return $r ?: ['ventes_jour' => 0, 'ca_jour' => 0, 'ca_mois' => 0];
    }
}
