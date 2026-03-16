<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport fournisseurs</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:10px; color:#222; }
.entete { text-align:center; border-bottom:2px solid #f59e0b; padding-bottom:8px; margin-bottom:12px; }
.entete h1 { font-size:16px; color:#f59e0b; }
table { width:100%; border-collapse:collapse; margin-bottom:12px; }
thead tr { background:#f59e0b; color:white; }
thead th { padding:5px 6px; font-size:9px; }
tbody tr:nth-child(even) { background:#fffbeb; }
tbody td { padding:4px 6px; border-bottom:1px solid #eee; }
.pied { text-align:center; border-top:1px solid #ddd; padding-top:6px; color:#999; font-size:9px; margin-top:15px; }
@media print { .no-print { display:none; } body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
</style>
</head>
<body>
<div class="no-print" style="padding:10px;background:#fffbeb;margin-bottom:10px;">
    <button onclick="window.print()" style="background:#f59e0b;color:white;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;">🖨️ Imprimer</button>
    <a href="javascript:window.close()" style="color:#666;margin-left:10px;">✕ Fermer</a>
</div>
<div class="entete">
    <h1>PHARMACIE [NOM]</h1>
    <p>Rapport fournisseurs au <?= date('d/m/Y') ?></p>
</div>
<table>
    <thead><tr><th>#</th><th>Fournisseur</th><th>Telephone</th><th style="text-align:center">Commandes</th><th style="text-align:right">Total achats</th><th style="text-align:center">Derniere cmd</th></tr></thead>
    <tbody>
    <?php foreach ($stats as $i => $f): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:bold"><?= htmlspecialchars($f['raison_sociale']) ?></td>
        <td><?= htmlspecialchars($f['telephone'] ?? '—') ?></td>
        <td style="text-align:center"><?= $f['nb_commandes'] ?></td>
        <td style="text-align:right;font-weight:bold"><?= number_format($f['total_achats'], 0, ',', ' ') ?> F</td>
        <td style="text-align:center"><?= $f['derniere_commande'] ? date('d/m/Y', strtotime($f['derniere_commande'])) : '—' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="pied">PharmaSys — <?= date('d/m/Y à H:i') ?></div>
</body>
</html>
