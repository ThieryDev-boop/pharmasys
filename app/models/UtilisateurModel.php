<?php
// app/models/UtilisateurModel.php

class UtilisateurModel extends Model {

    // ── AUTHENTIFICATION (AuthController) ────────────────────

    public function findByLogin(string $login) {
        return $this->queryOne(
            "SELECT * FROM utilisateurs WHERE login = :login AND actif = 1 LIMIT 1",
            [':login' => $login]
        );
    }

    public function incrementTentatives(int $id): void {
        // Colonnes tentatives_echec / bloque_jusqu_a optionnelles
        try {
            $this->execute(
                "UPDATE utilisateurs SET tentatives_echec = tentatives_echec + 1
                 WHERE id_utilisateur = :id",
                [':id' => $id]
            );
            $user = $this->findById($id);
            if ($user && ($user['tentatives_echec'] ?? 0) >= 5) {
                $this->execute(
                    "UPDATE utilisateurs SET bloque_jusqu_a = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                     WHERE id_utilisateur = :id",
                    [':id' => $id]
                );
            }
        } catch (PDOException $e) { /* Colonnes absentes — on ignore */ }
    }

    public function resetTentatives(int $id): void {
        try {
            $this->execute(
                "UPDATE utilisateurs SET tentatives_echec = 0, bloque_jusqu_a = NULL,
                 derniere_connexion = NOW() WHERE id_utilisateur = :id",
                [':id' => $id]
            );
        } catch (PDOException $e) {
            // Fallback si certaines colonnes absentes
            try {
                $this->execute(
                    "UPDATE utilisateurs SET derniere_connexion = NOW()
                     WHERE id_utilisateur = :id",
                    [':id' => $id]
                );
            } catch (PDOException $e2) { /* Ignore */ }
        }
    }

    public function updateDerniereConnexion(int $id): void {
        try {
            $this->execute(
                "UPDATE utilisateurs SET derniere_connexion = NOW(), tentatives_echec = 0
                 WHERE id_utilisateur = :id",
                [':id' => $id]
            );
        } catch (PDOException $e) {
            try {
                $this->execute(
                    "UPDATE utilisateurs SET derniere_connexion = NOW()
                     WHERE id_utilisateur = :id",
                    [':id' => $id]
                );
            } catch (PDOException $e2) { /* Ignore */ }
        }
    }

    // ── CRUD (AdminController) ────────────────────────────────

    public function findAll(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT id_utilisateur, nom, prenom, login, role, actif,
                       date_creation, derniere_connexion
                FROM utilisateurs WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (nom LIKE :s OR prenom LIKE :s2 OR login LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $sql .= " ORDER BY role ASC, nom ASC";
        $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (nom LIKE :s OR prenom LIKE :s2 OR login LIKE :s3)";
            $params[':s']  = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id) {
        return $this->queryOne(
            "SELECT id_utilisateur, nom, prenom, login, role, actif,
                    date_creation, derniere_connexion
             FROM utilisateurs WHERE id_utilisateur = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public function emailExiste(string $email, int $excludeId = 0): bool {
        // Colonne email absente de la table — desactivee
        return false;
    }

    public function loginExiste(string $login, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM utilisateurs
             WHERE login = :login AND id_utilisateur != :id"
        );
        $stmt->execute([':login' => $login, ':id' => $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int {
        $login = !empty($data['login'])
            ? $data['login']
            : $this->genererLogin($data['nom'], $data['prenom'] ?? '');

        return (int)$this->insert(
            "INSERT INTO utilisateurs (nom, prenom, login, password_hash, role)
             VALUES (:nom, :prenom, :login, :hash, :role)",
            [
                ':nom'    => $data['nom'],
                ':prenom' => $data['prenom'] ?: null,
                ':login'  => $login,
                ':hash'   => password_hash($data['mot_de_passe'], PASSWORD_BCRYPT, ['cost' => 12]),
                ':role'   => $data['role'],
            ]
        );
    }

    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE utilisateurs SET nom=:nom, prenom=:prenom, role=:role
             WHERE id_utilisateur=:id",
            [
                ':nom'    => $data['nom'],
                ':prenom' => $data['prenom'] ?: null,
                ':role'   => $data['role'],
                ':id'     => $id,
            ]
        );
    }

    public function changerMotDePasse(int $id, string $nouveauMdp): bool {
        return $this->execute(
            "UPDATE utilisateurs SET password_hash = :hash WHERE id_utilisateur = :id",
            [
                ':hash' => password_hash($nouveauMdp, PASSWORD_BCRYPT, ['cost' => 12]),
                ':id'   => $id,
            ]
        );
    }

    public function updatePassword(int $id, string $newPassword): void {
        $this->changerMotDePasse($id, $newPassword);
    }

    public function toggleActif(int $id): void {
        $this->execute(
            "UPDATE utilisateurs SET actif = NOT actif WHERE id_utilisateur = :id",
            [':id' => $id]
        );
    }

    public function verifierMotDePasse(int $id, string $mdp): bool {
        $stmt = $this->pdo->prepare(
            "SELECT password_hash FROM utilisateurs WHERE id_utilisateur = :id"
        );
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();
        return $hash && password_verify($mdp, $hash);
    }

    public function getStats(): array {
        return $this->query(
            "SELECT role,
                    COUNT(*) AS nb_total,
                    SUM(actif) AS nb_actifs
             FROM utilisateurs
             GROUP BY role ORDER BY role ASC"
        );
    }

    private function genererLogin(string $nom, string $prenom): string {
        $base  = strtolower(substr($prenom ?: '', 0, 1) . $nom);
        $base  = preg_replace('/[^a-z0-9]/', '', $base);
        $login = $base ?: 'user';
        $i     = 1;
        while ($this->loginExiste($login)) {
            $login = $base . $i++;
        }
        return $login;
    }
}