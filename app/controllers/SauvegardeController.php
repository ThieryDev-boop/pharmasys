<?php
// app/controllers/SauvegardeController.php

class SauvegardeController extends Controller {

    // Dossier de stockage des sauvegardes (relatif a la racine du projet)
    private string $backupDir;

    public function __construct() {
        Auth::requireAuth();
        Auth::requireRole('administrateur');
        // Dossier sauvegardes hors du public/
        $this->backupDir = dirname(APP_PATH) . '/sauvegardes';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    // Page principale
    public function index(): void {
        $sauvegardes = $this->listerSauvegardes();
        $csrfToken   = Auth::generateCsrfToken();
        $success     = $_SESSION['backup_success'] ?? '';
        $error       = $_SESSION['backup_error']   ?? '';
        unset($_SESSION['backup_success'], $_SESSION['backup_error']);

        $this->renderLayout('main', 'admin/sauvegardes', compact(
            'sauvegardes', 'csrfToken', 'success', 'error'
        ));
    }

    // Creer une sauvegarde
    public function creer(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        try {
            $fichier = $this->genererSauvegarde();
            $_SESSION['backup_success'] = $fichier;
        } catch (Exception $e) {
            $_SESSION['backup_error'] = $e->getMessage();
        }

        $this->redirect(BASE_URL . '/admin/sauvegardes');
    }

    // Telecharger une sauvegarde
    public function telecharger(): void {
        $fichier = basename($_GET['fichier'] ?? '');
        if (!$fichier || !preg_match('/^pharmasys_backup_[\d_]+\.sql$/', $fichier)) {
            die('Fichier invalide.');
        }

        $chemin = $this->backupDir . '/' . $fichier;
        if (!file_exists($chemin)) {
            die('Fichier introuvable.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fichier . '"');
        header('Content-Length: ' . filesize($chemin));
        readfile($chemin);
        exit;
    }

    // Supprimer une sauvegarde
    public function supprimer(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $fichier = basename($_POST['fichier'] ?? '');
        if (!$fichier || !preg_match('/^pharmasys_backup_[\d_]+\.sql$/', $fichier)) {
            $this->redirect(BASE_URL . '/admin/sauvegardes');
            return;
        }

        $chemin = $this->backupDir . '/' . $fichier;
        if (file_exists($chemin)) {
            unlink($chemin);
            $_SESSION['backup_success'] = 'supprimee';
        }

        $this->redirect(BASE_URL . '/admin/sauvegardes');
    }

    // ── Génération SQL via PDO ────────────────────────────────

    private function genererSauvegarde(): string {
        $config  = require CONFIG_PATH . '/database.php';
        $dsn     = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo     = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $dbName  = $config['dbname'];
        $date    = date('Y_m_d_H_i_s');
        $fichier = "pharmasys_backup_{$date}.sql";
        $chemin  = $this->backupDir . '/' . $fichier;

        $sql  = "-- PharmaSys - Sauvegarde de la base de donnees\n";
        $sql .= "-- Base : {$dbName}\n";
        $sql .= "-- Date : " . date('d/m/Y H:i:s') . "\n";
        $sql .= "-- Genere par : PharmaSys Admin\n";
        $sql .= "-- ------------------------------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        // Lister toutes les tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $sql .= $this->exporterTable($pdo, $table);
        }

        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "-- Fin de la sauvegarde\n";

        file_put_contents($chemin, $sql);

        // Garder seulement les 10 dernieres sauvegardes
        $this->nettoyerAnciennes(10);

        return $fichier;
    }

    private function exporterTable(PDO $pdo, string $table): string {
        $sql  = "\n-- ------------------------------------------------\n";
        $sql .= "-- Table : `{$table}`\n";
        $sql .= "-- ------------------------------------------------\n\n";

        // Structure
        $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= $create['Create Table'] . ";\n\n";

        // Données
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) return $sql;

        // Construire INSERT par lots de 100
        $cols    = '`' . implode('`, `', array_keys($rows[0])) . '`';
        $chunks  = array_chunk($rows, 100);

        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $escaped = array_map(function($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, $row);
                $values[] = '(' . implode(', ', $escaped) . ')';
            }
            $sql .= "INSERT INTO `{$table}` ({$cols}) VALUES\n";
            $sql .= implode(",\n", $values) . ";\n";
        }

        return $sql . "\n";
    }

    private function listerSauvegardes(): array {
        $fichiers = glob($this->backupDir . '/pharmasys_backup_*.sql') ?: [];
        $result   = [];

        foreach ($fichiers as $chemin) {
            $nom      = basename($chemin);
            $taille   = filesize($chemin);
            $date     = filemtime($chemin);
            $result[] = [
                'nom'        => $nom,
                'taille'     => $taille,
                'date'       => $date,
                'taille_fmt' => $this->formatTaille($taille),
                'date_fmt'   => date('d/m/Y H:i:s', $date),
            ];
        }

        // Plus recent en premier
        usort($result, fn($a, $b) => $b['date'] - $a['date']);
        return $result;
    }

    private function nettoyerAnciennes(int $garder): void {
        $fichiers = glob($this->backupDir . '/pharmasys_backup_*.sql') ?: [];
        usort($fichiers, fn($a, $b) => filemtime($b) - filemtime($a));
        foreach (array_slice($fichiers, $garder) as $vieux) {
            unlink($vieux);
        }
    }

    private function formatTaille(int $octets): string {
        if ($octets >= 1048576) return round($octets / 1048576, 1) . ' Mo';
        if ($octets >= 1024)    return round($octets / 1024, 1)    . ' Ko';
        return $octets . ' o';
    }
}
