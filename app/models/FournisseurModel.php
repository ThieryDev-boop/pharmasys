<?php
// app/models/FournisseurModel.php

class FournisseurModel extends Model {

    // Liste paginee
    public function findAll(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT f.*,
                    COUNT(DISTINCT c.id_commande) AS nb_commandes,
                    COALESCE(SUM(c.montant_total), 0) AS total_commandes,
                    MAX(c.date_commande) AS derniere_commande
                FROM fournisseurs f
                LEFT JOIN commandes c ON f.id_fournisseur = c.id_fournisseur
                WHERE f.actif = 1";
        $params = [];
        if ($search) {
            $sql .= " AND (f.raison_sociale LIKE :s OR f.contact LIKE :s2 OR f.telephone LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $sql .= " GROUP BY f.id_fournisseur ORDER BY f.raison_sociale ASC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM fournisseurs WHERE actif = 1";
        $params = [];
        if ($search) {
            $sql .= " AND (raison_sociale LIKE :s OR contact LIKE :s2 OR telephone LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Tous les fournisseurs actifs (pour select)
    public function findAllActifs(): array {
        return $this->query(
            "SELECT * FROM fournisseurs WHERE actif = 1 ORDER BY raison_sociale ASC"
        );
    }

    public function findById(int $id) {
        return $this->queryOne(
            "SELECT * FROM fournisseurs WHERE id_fournisseur = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): int {
        return (int) $this->insert(
            "INSERT INTO fournisseurs (raison_sociale, contact, telephone, email, adresse)
             VALUES (:rs, :ct, :tel, :email, :adr)",
            [
                ':rs'    => $data['raison_sociale'],
                ':ct'    => !empty($data['contact'])   ? $data['contact']   : null,
                ':tel'   => !empty($data['telephone'])  ? $data['telephone'] : null,
                ':email' => !empty($data['email'])      ? $data['email']     : null,
                ':adr'   => !empty($data['adresse'])    ? $data['adresse']   : null,
            ]
        );
    }

    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE fournisseurs SET
             raison_sociale = :rs,
             contact        = :ct,
             telephone      = :tel,
             email          = :email,
             adresse        = :adr
             WHERE id_fournisseur = :id",
            [
                ':rs'    => $data['raison_sociale'],
                ':ct'    => !empty($data['contact'])   ? $data['contact']   : null,
                ':tel'   => !empty($data['telephone'])  ? $data['telephone'] : null,
                ':email' => !empty($data['email'])      ? $data['email']     : null,
                ':adr'   => !empty($data['adresse'])    ? $data['adresse']   : null,
                ':id'    => $id,
            ]
        );
    }

    public function toggleActif(int $id): void {
        $this->execute(
            "UPDATE fournisseurs SET actif = NOT actif WHERE id_fournisseur = :id",
            [':id' => $id]
        );
    }

    // Historique des commandes d'un fournisseur
    public function getCommandes(int $id): array {
        return $this->query(
            "SELECT c.*, CONCAT(u.nom,' ',u.prenom) AS createur_nom
             FROM commandes c
             JOIN utilisateurs u ON c.id_utilisateur = u.id_utilisateur
             WHERE c.id_fournisseur = :id
             ORDER BY c.date_commande DESC",
            [':id' => $id]
        );
    }

    // Stats d'un fournisseur
    public function getStats(int $id) {
        return $this->queryOne(
            "SELECT
                COUNT(*) AS nb_commandes,
                COALESCE(SUM(montant_total), 0) AS total_commandes,
                COALESCE(AVG(montant_total), 0) AS montant_moyen,
                MAX(date_commande) AS derniere_commande
             FROM commandes
             WHERE id_fournisseur = :id",
            [':id' => $id]
        );
    }
}
