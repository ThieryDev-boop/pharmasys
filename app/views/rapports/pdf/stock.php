<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Etat du stock — <?= date('d/m/Y') ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:10px; color:#222; }
.entete { text-align:center; border-bottom:2px solid #2E75B6; padding-bottom:8px; margin-bottom:12px; }
.entete h1 { font-size:16px; color:#2E75B6; }
.kpis { display:flex; gap:8px; margin-bottom:12px; }
.kpi { flex:1; border:1px solid #ddd; border-radius:3px; padding:6px; text-align:center; }
.kpi .val { font-size:14px; font-weight:bold; color:#2E75B6; }
.kpi .lbl { font-size:9px; color:#888; }
table { width:100%; border-collapse:collapse; margin-bottom:12px; }
thead tr { background:#2E75B6; color:white; }
thead th { padding:4px 6px; font-size:9px; }
tbody tr:nth-child(even) { background:#f5f9ff; }
tbody td { padding:3px 6px; border-bottom:1px solid #eee; font-size:9px; }
.alerte { background:#fff3cd !important; }
.rupture { background:#f8d7da !important; }
.section { font-size:11px; font-weight:bold; color:#1a3a5c; margin:10px 0 4px;
           border-left:3px solid #2E75B6; padding-left:6px; }
.pied { text-align:center; border-top:1px solid #ddd; padding-top:6px;
        color:#999; font-size:9px; margin-top:15px; }
@media print {
    body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none; }
}
</style>
</head>
<body>

<div class="no-print" style="padding:10px;background:#f0f7ff;margin-bottom:10px;">
    <button onclick="window.print()" style="background:#2E75B6;color:white;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;margin-right:10px;">
        🖨️ Imprimer
    </button>
    <a href="javascript:window.close()" style="color:#666;">✕ Fermer</a>
</div>

<div class="entete">
    <h1>PHARMACIE [NOM]</h1>
    <p>Etat du stock au <?= date('d/m/Y à H:i') ?></p>
</div>

<!-- KPIs -->
<div class="kpis">
    <div class="kpi">
        <div class="val"><?= $valeur['nb_references'] ?? 0 ?></div>
        <div class="lbl">References</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($valeur['total_unites'] ?? 0, 0, ',', ' ') ?></div>
        <div class="lbl">Total unites</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($valeur['valeur_achat'] ?? 0, 0, ',', ' ') ?> F</div>
        <div class="lbl">Valeur achat</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($valeur['valeur_vente'] ?? 0, 0, ',', ' ') ?> F</div>
        <div class="lbl">Valeur vente</div>
    </div>
    <div class="kpi">
        <div class="val" style="color:#dc3545"><?= count($alertes) ?></div>
        <div class="lbl">Alertes</div>
    </div>
</div>

<!-- Alertes -->
<?php if (!empty($alertes)): ?>
<div class="section">⚠️ Alertes stock (<?= count($alertes) ?>)</div>
<table>
    <thead><tr><th>Medicament</th><th>DCI</th><th style="text-align:center">Stock</th><th style="text-align:center">Seuil</th><th style="text-align:center">Etat</th></tr></thead>
    <tbody>
    <?php foreach ($alertes as $a): ?>
    <tr class="<?= $a['type_alerte'] === 'rupture' ? 'rupture' : 'alerte' ?>">
        <td style="font-weight:bold"><?= htmlspecialchars($a['nom_commercial']) ?></td>
        <td><?= htmlspecialchars($a['dci'] ?? '') ?></td>
        <td style="text-align:center;font-weight:bold"><?= $a['stock_total'] ?></td>
        <td style="text-align:center"><?= $a['seuil_minimum'] ?></td>
        <td style="text-align:center;font-weight:bold"><?= $a['type_alerte'] === 'rupture' ? 'RUPTURE' : 'ALERTE' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Peremptions -->
<?php if (!empty($peremptions)): ?>
<div class="section">📅 Peremptions (<?= count($peremptions) ?> lots)</div>
<table>
    <thead><tr><th>Medicament</th><th>N° Lot</th><th style="text-align:center">Peremption</th><th style="text-align:center">Jours</th><th style="text-align:center">Qte</th></tr></thead>
    <tbody>
    <?php foreach ($peremptions as $p): ?>
    <tr class="<?= $p['jours_restants'] <= 30 ? 'rupture' : 'alerte' ?>">
        <td><?= htmlspecialchars($p['nom_commercial']) ?></td>
        <td><?= htmlspecialchars($p['numero_lot']) ?></td>
        <td style="text-align:center"><?= date('d/m/Y', strtotime($p['date_peremption'])) ?></td>
        <td style="text-align:center;font-weight:bold"><?= $p['jours_restants'] ?>j</td>
        <td style="text-align:center"><?= $p['quantite_restante'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Inventaire complet -->
<div class="section">Inventaire complet (<?= count($etatStock) ?> references)</div>
<table>
    <thead>
        <tr>
            <th>Medicament</th>
            <th>DCI</th>
            <th>Categorie</th>
            <th style="text-align:center">Stock</th>
            <th style="text-align:center">Seuil</th>
            <th style="text-align:right">Prix vente</th>
            <th style="text-align:center">Statut</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($etatStock as $m):
        $stock = (int)$m['stock_total'];
        $seuil = (int)$m['seuil_minimum'];
        $classe = $stock == 0 ? 'rupture' : ($stock <= $seuil ? 'alerte' : '');
        $statut = $stock == 0 ? 'RUPTURE' : ($stock <= $seuil ? 'ALERTE' : 'OK');
    ?>
    <tr class="<?= $classe ?>">
        <td style="font-weight:bold"><?= htmlspecialchars($m['nom_commercial']) ?></td>
        <td><?= htmlspecialchars($m['dci'] ?? '') ?></td>
        <td><?= htmlspecialchars($m['nom_categorie'] ?? '') ?></td>
        <td style="text-align:center;font-weight:bold"><?= $stock ?></td>
        <td style="text-align:center"><?= $seuil ?></td>
        <td style="text-align:right"><?= number_format($m['prix_vente'], 0, ',', ' ') ?> F</td>
        <td style="text-align:center;font-weight:bold"><?= $statut ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="pied">PharmaSys — Rapport genere le <?= date('d/m/Y à H:i') ?></div>
</body>
</html>
