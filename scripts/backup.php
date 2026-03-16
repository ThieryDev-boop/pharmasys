<?php
// scripts/backup.php - Appelé par cron : 0 2 * * * php /path/to/backup.php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$config    = require ROOT_PATH . '/config/database.php';
$backupDir = ROOT_PATH . '/storage/backups/';
$maxBackups = 30; // Conserver 30 jours
$filename   = $backupDir . 'pharmasys_' . date('Y-m-d_H-i-s') . '.sql';

// Génération du dump MySQL
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines %s > %s 2>&1',
    escapeshellarg($config['host']),
    escapeshellarg($config['username']),
    escapeshellarg($config['password']),
    escapeshellarg($config['dbname']),
    escapeshellarg($filename)
);

exec($command, $output, $returnCode);

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
    $config['username'], $config['password']
);

if ($returnCode === 0) {
    $taille = filesize($filename);
    $pdo->prepare("INSERT INTO sauvegardes (nom_fichier, taille_fichier, type, statut)
                   VALUES (:f, :t, 'automatique', 'reussi')")
        ->execute([':f' => basename($filename), ':t' => $taille]);
    echo "Sauvegarde réussie: " . basename($filename) . "
";
} else {
    $pdo->prepare("INSERT INTO sauvegardes (nom_fichier, taille_fichier, type, statut)
                   VALUES (:f, 0, 'automatique', 'echoue')")
        ->execute([':f' => basename($filename)]);
    echo "Erreur sauvegarde: " . implode("
", $output) . "
";
}

// Nettoyage des anciennes sauvegardes (> 30)
$backups = glob($backupDir . '*.sql');
usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));
foreach (array_slice($backups, $maxBackups) as $oldBackup) {
    unlink($oldBackup);
}
