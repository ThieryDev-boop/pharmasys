<?php
// app/models/ClientModel.php

class ClientModel extends Model {

    // Liste paginee
    public function findAllPagine(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT c.*,
                    COUNT(v.id_vente) AS nb_achats,
                    COALESCE(SUM(v.montant_total_ttc), 0) AS total_achats,
                    MAX(v.date_vente) AS dernier_achat
                FROM clients c
                LEFT JOIN ventes v ON c.id_client = v.id_client AND v.statut = 'validee'
                WHERE c.actif = 1";
        $params = [];
        if ($search) {
            $sql .= " AND (c.nom LIKE :s OR c.prenom LIKE :s2 OR c.telephone LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $sql .= " GROUP BY c.id_client ORDER BY c.nom ASC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Compter
    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM clients WHERE actif = 1";
        $params = [];
        if ($search) {
            $sql .= " AND (nom LIKE :s OR prenom LIKE :s2 OR telephone LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Tous les clients actifs (pour select)
    public function findAll(): array {
        return $this->query(
            "SELECT * FROM clients WHERE actif = 1 ORDER BY nom ASC"
        );
    }

    // Trouver par ID
    public function findById(int $id) {
        return $this->queryOne(
            "SELECT * FROM clients WHERE id_client = :id",
            [':id' => $id]
        );
    }

    // Recherche AJAX
    public function search(string $terme): array {
        return $this->query(
            "SELECT * FROM clients
             WHERE actif = 1
               AND (nom LIKE :s OR prenom LIKE :s2 OR telephone LIKE :s3)
             ORDER BY nom ASC LIMIT 10",
            [':s' => "%$terme%", ':s2' => "%$terme%", ':s3' => "%$terme%"]
        );
    }

    // Creer un client
    public function create(array $data): int {
        return (int) $this->insert(
            "INSERT INTO clients (nom, prenom, telephone, adresse)
             VALUES (:nom, :prenom, :tel, :adr)",
            [
                ':nom'    => $data['nom'],
                ':prenom' => !empty($data['prenom'])    ? $data['prenom']    : null,
                ':tel'    => !empty($data['telephone'])  ? $data['telephone'] : null,
                ':adr'    => !empty($data['adresse'])    ? $data['adresse']   : null,
            ]
        );
    }

    // Modifier un client
    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE clients SET nom=:nom, prenom=:prenom, telephone=:tel, adresse=:adr
             WHERE id_client = :id",
            [
                ':nom'    => $data['nom'],
                ':prenom' => !empty($data['prenom'])   ? $data['prenom']    : null,
                ':tel'    => !empty($data['telephone']) ? $data['telephone'] : null,
                ':adr'    => !empty($data['adresse'])   ? $data['adresse']   : null,
                ':id'     => $id,
            ]
        );
    }

    // Activer / desactiver
    public function toggleActif(int $id): void {
        $this->execute(
            "UPDATE clients SET actif = NOT actif WHERE id_client = :id",
            [':id' => $id]
        );
    }

    // Historique des achats d'un client
    public function getHistoriqueAchats(int $id): array {
        return $this->query(
            "SELECT v.*, CONCAT(u.nom,' ',u.prenom) AS caissier_nom,
                    COUNT(lv.id_ligne) AS nb_lignes
             FROM ventes v
             JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
             LEFT JOIN lignes_vente lv ON v.id_vente = lv.id_vente
             WHERE v.id_client = :id AND v.statut = 'validee'
             GROUP BY v.id_vente
             ORDER BY v.date_vente DESC",
            [':id' => $id]
        );
    }

    // Stats globales d'un client
    public function getStats(int $id) {
        return $this->queryOne(
            "SELECT
                COUNT(*) AS nb_achats,
                COALESCE(SUM(montant_total_ttc), 0) AS total_depense,
                COALESCE(AVG(montant_total_ttc), 0) AS panier_moyen,
                MAX(date_vente) AS dernier_achat,
                MIN(date_vente) AS premier_achat
             FROM ventes
             WHERE id_client = :id AND statut = 'validee'",
            [':id' => $id]
        );
    }
}
