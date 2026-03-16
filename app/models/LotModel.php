<?php
// app/models/LotModel.php

class LotModel extends Model {

    // Liste des lots avec filtres
    public function findAll(string $filtre = 'tous', string $search = '', int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT l.*, m.nom_commercial, m.dci,
                    DATEDIFF(l.date_peremption, CURDATE()) AS jours_restants
                FROM lots l
                JOIN medicaments m ON l.id_medicament = m.id_medicament
                WHERE 1=1";

        $params = [];

        // Filtre
        if ($filtre === 'actif') {
            $sql .= " AND l.quantite_restante > 0 AND l.date_peremption > CURDATE()";
        } elseif ($filtre === 'perime') {
            $sql .= " AND l.date_peremption < CURDATE()";
        } elseif ($filtre === 'alerte') {
            $sql .= " AND l.date_peremption <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                      AND l.date_peremption > CURDATE()
                      AND l.quantite_restante > 0";
        } elseif ($filtre === 'epuise') {
            $sql .= " AND l.quantite_restante = 0";
        }

        // Recherche
        if ($search) {
            $sql .= " AND (m.nom_commercial LIKE :s OR l.numero_lot LIKE :s2)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
        }

        $sql .= " ORDER BY l.date_peremption ASC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Compter pour pagination
    public function countAll(string $filtre = 'tous', string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM lots l
                JOIN medicaments m ON l.id_medicament = m.id_medicament
                WHERE 1=1";
        $params = [];

        if ($filtre === 'actif') {
            $sql .= " AND l.quantite_restante > 0 AND l.date_peremption > CURDATE()";
        } elseif ($filtre === 'perime') {
            $sql .= " AND l.date_peremption < CURDATE()";
        } elseif ($filtre === 'alerte') {
            $sql .= " AND l.date_peremption <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                      AND l.date_peremption > CURDATE()
                      AND l.quantite_restante > 0";
        } elseif ($filtre === 'epuise') {
            $sql .= " AND l.quantite_restante = 0";
        }

        if ($search) {
            $sql .= " AND (m.nom_commercial LIKE :s OR l.numero_lot LIKE :s2)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Selectionner les lots selon la regle FEFO (First Expired First Out)
    public function getLotsFEFO(int $idMedicament, int $quantiteRequise): array {
        $lots = $this->query(
            "SELECT * FROM lots
             WHERE id_medicament = :id
               AND quantite_restante > 0
               AND date_peremption > CURDATE()
             ORDER BY date_peremption ASC",
            [':id' => $idMedicament]
        );

        $selection = [];
        $restant   = $quantiteRequise;

        foreach ($lots as $lot) {
            if ($restant <= 0) break;
            $qteFromLot  = min($lot['quantite_restante'], $restant);
            $selection[] = ['lot' => $lot, 'quantite' => $qteFromLot];
            $restant    -= $qteFromLot;
        }

        if ($restant > 0) return []; // Stock insuffisant
        return $selection;
    }

    // Decrementer la quantite d'un lot
    public function decrementer(int $idLot, int $quantite): bool {
        return $this->execute(
            "UPDATE lots SET quantite_restante = quantite_restante - :q
             WHERE id_lot = :id AND quantite_restante >= :q2",
            [':q' => $quantite, ':id' => $idLot, ':q2' => $quantite]
        );
    }

    // Restituer la quantite d'un lot (annulation vente)
    public function restituer(int $idLot, int $quantite): bool {
        return $this->execute(
            "UPDATE lots SET quantite_restante = quantite_restante + :q
             WHERE id_lot = :id",
            [':q' => $quantite, ':id' => $idLot]
        );
    }

    // Creer un nouveau lot
    public function create(array $data): int {
        return (int) $this->insert(
            "INSERT INTO lots
             (id_medicament, numero_lot, date_fabrication, date_peremption,
              quantite_initiale, quantite_restante, prix_achat, id_commande)
             VALUES (:idm, :num, :dfab, :dper, :qi, :qi2, :pa, :cmd)",
            [
                ':idm'  => (int)$data['id_medicament'],
                ':num'  => $data['numero_lot'],
                ':dfab' => !empty($data['date_fabrication']) ? $data['date_fabrication'] : null,
                ':dper' => $data['date_peremption'],
                ':qi'   => (int)$data['quantite'],
                ':qi2'  => (int)$data['quantite'],
                ':pa'   => (float)($data['prix_achat'] ?? 0),
                ':cmd'  => !empty($data['id_commande']) ? (int)$data['id_commande'] : null,
            ]
        );
    }

    // Alertes peremption (lots non epuises expirant dans X jours)
    public function getAlertesPeremption(int $jours = 90): array {
        return $this->query(
            "SELECT l.*, m.nom_commercial, m.dci,
                    DATEDIFF(l.date_peremption, CURDATE()) AS jours_restants
             FROM lots l
             JOIN medicaments m ON l.id_medicament = m.id_medicament
             WHERE l.quantite_restante > 0
               AND l.date_peremption <= DATE_ADD(CURDATE(), INTERVAL :j DAY)
             ORDER BY l.date_peremption ASC",
            [':j' => $jours]
        );
    }

    // Lots d'un medicament
    public function findByMedicament(int $idMedicament): array {
        return $this->query(
            "SELECT * FROM lots WHERE id_medicament = :id ORDER BY date_peremption ASC",
            [':id' => $idMedicament]
        );
    }
}
