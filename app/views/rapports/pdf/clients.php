<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport clients</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:10px; color:#222; }
.entete { text-align:center; border-bottom:2px solid #2E75B6; padding-bottom:8px; margin-bottom:12px; }
.entete h1 { font-size:16px; color:#2E75B6; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#2E75B6; color:white; }
thead th { padding:5px 6px; font-size:9px; }
tbody tr:nth-child(even) { background:#f5f9ff; }
tbody td { padding:4px 6px; border-bottom:1px solid #eee; }
.pied { text-align:center; border-top:1px solid #ddd; padding-top:6px; color:#999; font-size:9px; margin-top:15px; }
@media print { .no-print { display:none; } body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
</style>
</head>
<body>
<div class="no-print" style="padding:10px;background:#f0f7ff;margin-bottom:10px;">
    <button onclick="window.print()" style="background:#2E75B6;color:white;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;">🖨️ Imprimer</button>
    <a href="javascript:window.close()" style="color:#666;margin-left:10px;">✕ Fermer</a>
</div>
<div class="entete">
    <h1>PHARMACIE [NOM]</h1>
    <p>Top clients — <?= date('d/m/Y') ?></p>
</div>
<table>
    <thead><tr><th>#</th><th>Client</th><th>Telephone</th><th style="text-align:center">Achats</th><th style="text-align:right">Total depense</th><th style="text-align:right">Panier moyen</th><th style="text-align:center">Dernier achat</th></tr></thead>
    <tbody>
    <?php foreach ($clients as $i => $c): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:bold"><?= htmlspecialchars($c['client_nom']) ?></td>
        <td><?= htmlspecialchars($c['telephone'] ?? '—') ?></td>
        <td style="text-align:center"><?= $c['nb_achats'] ?></td>
        <td style="text-align:right;font-weight:bold;color:#16a34a"><?= number_format($c['total_depense'], 0, ',', ' ') ?> F</td>
        <td style="text-align:right"><?= number_format($c['panier_moyen'], 0, ',', ' ') ?> F</td>
        <td style="text-align:center"><?= date('d/m/Y', strtotime($c['dernier_achat'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="pied">PharmaSys — <?= date('d/m/Y à H:i') ?></div>
</body>
</html>
