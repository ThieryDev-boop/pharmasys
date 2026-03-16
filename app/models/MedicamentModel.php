<?php
// app/models/MedicamentModel.php

class MedicamentModel extends Model {

    // Liste paginee avec stock total
    public function findAll(int $page = 1, int $perPage = 25, string $search = ''): array {
        $offset = ($page - 1) * $perPage;

        // On injecte LIMIT et OFFSET directement dans la requete (valeurs entières, sans risque d'injection)
        $sql = "SELECT m.*, c.nom AS categorie_nom,
                    COALESCE(SUM(l.quantite_restante), 0) AS stock_total
                FROM medicaments m
                LEFT JOIN categories c ON m.id_categorie = c.id_categorie
                LEFT JOIN lots l ON m.id_medicament = l.id_medicament
                    AND l.date_peremption > CURDATE()
                WHERE m.statut = 'actif'";

        $params = [];
        if ($search) {
            $sql .= " AND (m.nom_commercial LIKE :s OR m.dci LIKE :s2 OR m.code_barres LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }

        $sql .= " GROUP BY m.id_medicament ORDER BY m.nom_commercial ASC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Compter le total pour la pagination
    public function countAll(string $search = ''): int {
        $sql    = "SELECT COUNT(*) FROM medicaments WHERE statut = 'actif'";
        $params = [];
        if ($search) {
            $sql .= " AND (nom_commercial LIKE :s OR dci LIKE :s2 OR code_barres LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Trouver par ID
    public function findById(int $id) {
        return $this->queryOne(
            "SELECT m.*, c.nom AS nom_categorie
             FROM medicaments m
             LEFT JOIN categories c ON m.id_categorie = c.id_categorie
             WHERE m.id_medicament = :id",
            [':id' => $id]
        );
    }

    // Recherche rapide pour la vente (AJAX)
    public function searchByNom(string $terme): array {
        return $this->query(
            "SELECT m.id_medicament, m.nom_commercial, m.dci, m.forme_galenique,
                    m.dosage, m.prix_vente, m.ordonnance_requise,
                    COALESCE(SUM(l.quantite_restante), 0) AS stock_total
             FROM medicaments m
             LEFT JOIN lots l ON m.id_medicament = l.id_medicament
                 AND l.date_peremption > CURDATE()
             WHERE m.statut = 'actif'
               AND (m.nom_commercial LIKE :s OR m.dci LIKE :s2 OR m.code_barres LIKE :s3)
             GROUP BY m.id_medicament
             LIMIT 10",
            [':s' => "%$terme%", ':s2' => "%$terme%", ':s3' => "%$terme%"]
        );
    }

    // Creer un medicament
    public function create(array $data): int {
        return (int) $this->insert(
            "INSERT INTO medicaments
             (nom_commercial, dci, forme_galenique, dosage, id_categorie,
              conditionnement, code_barres, prix_achat, prix_vente, tva,
              seuil_minimum, ordonnance_requise)
             VALUES (:nom, :dci, :forme, :dosage, :cat, :cond, :cb, :pa, :pv, :tva, :seuil, :ord)",
            [
                ':nom'   => $data['nom_commercial'],
                ':dci'   => $data['dci']             ?? null,
                ':forme' => $data['forme_galenique'] ?? null,
                ':dosage'=> $data['dosage']          ?? null,
                ':cat'   => !empty($data['id_categorie']) ? (int)$data['id_categorie'] : null,
                ':cond'  => (int)($data['conditionnement'] ?? 1),
                ':cb'    => !empty($data['code_barres']) ? $data['code_barres'] : null,
                ':pa'    => (float)($data['prix_achat'] ?? 0),
                ':pv'    => (float)$data['prix_vente'],
                ':tva'   => (float)($data['tva'] ?? 0),
                ':seuil' => (int)($data['seuil_minimum'] ?? 5),
                ':ord'   => (int)($data['ordonnance_requise'] ?? 0),
            ]
        );
    }

    // Modifier un medicament
    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE medicaments SET
             nom_commercial     = :nom,
             dci                = :dci,
             forme_galenique    = :forme,
             dosage             = :dosage,
             id_categorie       = :cat,
             conditionnement    = :cond,
             code_barres        = :cb,
             prix_achat         = :pa,
             prix_vente         = :pv,
             tva                = :tva,
             seuil_minimum      = :seuil,
             ordonnance_requise = :ord
             WHERE id_medicament = :id",
            [
                ':nom'   => $data['nom_commercial'],
                ':dci'   => $data['dci']             ?? null,
                ':forme' => $data['forme_galenique'] ?? null,
                ':dosage'=> $data['dosage']          ?? null,
                ':cat'   => !empty($data['id_categorie']) ? (int)$data['id_categorie'] : null,
                ':cond'  => (int)($data['conditionnement'] ?? 1),
                ':cb'    => !empty($data['code_barres']) ? $data['code_barres'] : null,
                ':pa'    => (float)($data['prix_achat'] ?? 0),
                ':pv'    => (float)$data['prix_vente'],
                ':tva'   => (float)($data['tva'] ?? 0),
                ':seuil' => (int)($data['seuil_minimum'] ?? 5),
                ':ord'   => (int)($data['ordonnance_requise'] ?? 0),
                ':id'    => $id,
            ]
        );
    }

    // Archiver (suppression logique)
    public function archive(int $id): bool {
        return $this->execute(
            "UPDATE medicaments SET statut = 'archive' WHERE id_medicament = :id",
            [':id' => $id]
        );
    }

    // Alertes stock faible
    public function getAlertesStock(): array {
        return $this->query(
            "SELECT m.id_medicament, m.nom_commercial, m.dci, m.seuil_minimum,
                    COALESCE(SUM(l.quantite_restante), 0) AS stock_total
             FROM medicaments m
             LEFT JOIN lots l ON m.id_medicament = l.id_medicament
                 AND l.date_peremption > CURDATE()
             WHERE m.statut = 'actif'
             GROUP BY m.id_medicament
             HAVING stock_total <= m.seuil_minimum
             ORDER BY stock_total ASC"
        );
    }

    // Liste des categories
    public function getCategories(): array {
        return $this->query("SELECT * FROM categories ORDER BY nom ASC");
    }

    // Lots d'un medicament
    public function getLots(int $idMedicament): array {
        return $this->query(
            "SELECT * FROM lots WHERE id_medicament = :id ORDER BY date_peremption ASC",
            [':id' => $idMedicament]
        );
    }
}