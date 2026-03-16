<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($titre) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
.entete { text-align:center; border-bottom: 2px solid #2E75B6; padding-bottom:10px; margin-bottom:15px; }
.entete h1 { font-size:18px; color:#2E75B6; }
.entete p { color:#666; font-size:10px; }
.titre-rapport { font-size:14px; font-weight:bold; margin:10px 0 5px; color:#1a3a5c; }
.kpis { display:flex; gap:10px; margin-bottom:15px; }
.kpi { flex:1; border:1px solid #ddd; border-radius:4px; padding:8px; text-align:center; }
.kpi .val { font-size:16px; font-weight:bold; color:#2E75B6; }
.kpi .lbl { font-size:9px; color:#888; }
table { width:100%; border-collapse:collapse; margin-bottom:15px; }
thead tr { background:#2E75B6; color:white; }
thead th { padding:5px 8px; font-size:10px; }
tbody tr:nth-child(even) { background:#f5f9ff; }
tbody td { padding:4px 8px; border-bottom:1px solid #eee; }
.section { font-size:12px; font-weight:bold; color:#1a3a5c; margin:12px 0 5px;
           border-left:3px solid #2E75B6; padding-left:8px; }
.pied { text-align:center; border-top:1px solid #ddd; padding-top:8px;
        color:#999; font-size:9px; margin-top:20px; }
@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display:none; }
}
</style>
</head>
<body>

<div class="no-print" style="padding:10px; background:#f0f7ff; margin-bottom:10px;">
    <button onclick="window.print()" style="background:#2E75B6;color:white;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;margin-right:10px;">
        🖨️ Imprimer
    </button>
    <a href="javascript:window.close()" style="color:#666;">✕ Fermer</a>
</div>

<!-- En-tete -->
<div class="entete">
    <h1>PHARMACIE [NOM]</h1>
    <p>Adresse | Telephone | RCCM | NIF</p>
</div>

<div class="titre-rapport"><?= htmlspecialchars($titre) ?></div>
<p style="color:#888;font-size:9px;margin-bottom:10px;">
    Genere le <?= date('d/m/Y à H:i') ?>
</p>

<!-- KPIs -->
<div class="kpis">
    <div class="kpi">
        <div class="val"><?= $stats['nb_ventes'] ?? 0 ?></div>
        <div class="lbl">Ventes</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($stats['ca_total'] ?? 0, 0, ',', ' ') ?> F</div>
        <div class="lbl">CA Total</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($stats['panier_moyen'] ?? 0, 0, ',', ' ') ?> F</div>
        <div class="lbl">Panier moyen</div>
    </div>
    <div class="kpi">
        <div class="val"><?= number_format($stats['vente_max'] ?? 0, 0, ',', ' ') ?> F</div>
        <div class="lbl">Vente max</div>
    </div>
</div>

<!-- Par caissier -->
<?php if (!empty($parCaissier)): ?>
<div class="section">Ventes par caissier</div>
<table>
    <thead><tr><th>Caissier</th><th style="text-align:center">Nb ventes</th><th style="text-align:right">CA Total</th></tr></thead>
    <tbody>
    <?php foreach ($parCaissier as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['caissier']) ?></td>
        <td style="text-align:center"><?= $c['nb_ventes'] ?></td>
        <td style="text-align:right;font-weight:bold"><?= number_format($c['ca_total'], 0, ',', ' ') ?> F</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Top medicaments -->
<?php if (!empty($topMeds)): ?>
<div class="section">Top medicaments vendus</div>
<table>
    <thead><tr><th>#</th><th>Medicament</th><th style="text-align:center">Qte vendue</th><th style="text-align:right">CA genere</th></tr></thead>
    <tbody>
    <?php foreach ($topMeds as $i => $m): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td><?= htmlspecialchars($m['nom_commercial']) ?> <?= $m['dci'] ? '(' . htmlspecialchars($m['dci']) . ')' : '' ?></td>
        <td style="text-align:center;font-weight:bold"><?= $m['total_vendu'] ?></td>
        <td style="text-align:right"><?= number_format($m['ca_genere'], 0, ',', ' ') ?> F</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Detail des ventes -->
<div class="section">Detail des <?= count($ventes) ?> ventes</div>
<table>
    <thead>
        <tr>
            <th>N° Facture</th>
            <th>Date</th>
            <th>Client</th>
            <th>Caissier</th>
            <th style="text-align:right">Total TTC</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($ventes as $v): ?>
    <tr>
        <td style="font-weight:bold;color:#2E75B6"><?= htmlspecialchars($v['numero_facture']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
        <td><?= htmlspecialchars($v['client_nom']) ?></td>
        <td><?= htmlspecialchars($v['caissier_nom']) ?></td>
        <td style="text-align:right;font-weight:bold"><?= number_format($v['montant_total_ttc'], 0, ',', ' ') ?> F</td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#e8f0fe;font-weight:bold">
        <td colspan="4" style="text-align:right;padding-right:10px">TOTAL</td>
        <td style="text-align:right"><?= number_format($stats['ca_total'] ?? 0, 0, ',', ' ') ?> F</td>
    </tr>
    </tbody>
</table>

<div class="pied">
    PharmaSys — Rapport genere automatiquement le <?= date('d/m/Y à H:i') ?>
</div>
</body>
</html>
