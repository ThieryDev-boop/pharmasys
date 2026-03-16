<?php // app/views/rapports/ventes.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-graph-up text-success me-2"></i>Rapport des ventes
    </h4>
    <a href="<?= BASE_URL ?>/rapports" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<!-- Filtre periode -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/rapports/ventes"
              class="d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label class="form-label small fw-semibold mb-1">Date debut</label>
                <input type="date" name="date_debut" class="form-control form-control-sm"
                    value="<?= $dateDebut ?>">
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">Date fin</label>
                <input type="date" name="date_fin" class="form-control form-control-sm"
                    value="<?= $dateFin ?>">
            </div>
            <!-- Raccourcis -->
            <div class="d-flex gap-1">
                <?php
                $raccourcis = [
                    'Aujourd\'hui' => [date('Y-m-d'), date('Y-m-d')],
                    'Cette semaine'=> [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
                    'Ce mois'      => [date('Y-m-01'), date('Y-m-d')],
                    'Ce trimestre' => [date('Y-m-d', mktime(0,0,0, floor((date('n')-1)/3)*3+1, 1)), date('Y-m-d')],
                    'Cette annee'  => [date('Y-01-01'), date('Y-m-d')],
                ];
                foreach ($raccourcis as $label => $dates):
                ?>
                <a href="<?= BASE_URL ?>/rapports/ventes?date_debut=<?= $dates[0] ?>&date_fin=<?= $dates[1] ?>"
                   class="btn btn-sm <?= ($dateDebut === $dates[0] && $dateFin === $dates[1]) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i>Filtrer
            </button>
            <!-- Exports -->
            <div class="ms-auto d-flex gap-2">
                <a href="<?= BASE_URL ?>/rapports/ventes?date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>&export=pdf"
                   class="btn btn-sm btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <a href="<?= BASE_URL ?>/rapports/ventes?date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>&export=csv"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- KPIs periode -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Nb ventes</div>
            <div class="fw-bold fs-3 text-primary"><?= $stats['nb_ventes'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">CA Total</div>
            <div class="fw-bold fs-4 text-success">
                <?= number_format($stats['ca_total'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Panier moyen</div>
            <div class="fw-bold fs-5 text-info">
                <?= number_format($stats['panier_moyen'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Vente max</div>
            <div class="fw-bold fs-5 text-warning">
                <?= number_format($stats['vente_max'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- Graphique CA par jour -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-bar-chart text-success me-2"></i>Evolution du CA
            </div>
            <div class="card-body">
                <canvas id="chartVentes" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Par caissier -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-person-badge text-primary me-2"></i>Par caissier
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <?php foreach ($parCaissier as $c): ?>
                    <tr>
                        <td class="ps-3 small fw-semibold"><?= htmlspecialchars($c['caissier']) ?></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $c['nb_ventes'] ?></span></td>
                        <td class="text-end pe-3 small fw-semibold text-success">
                            <?= number_format($c['ca_total'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($parCaissier)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">Aucune donnee</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- Top medicaments -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-trophy text-warning me-2"></i>Top 10 medicaments vendus
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Medicament</th>
                            <th class="text-center">Qte</th>
                            <th class="text-end pe-3">CA</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topMeds as $i => $m): ?>
                    <tr>
                        <td class="ps-3 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <div class="small fw-semibold"><?= htmlspecialchars($m['nom_commercial']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($m['dci'] ?? '') ?></small>
                        </td>
                        <td class="text-center fw-bold"><?= $m['total_vendu'] ?></td>
                        <td class="text-end pe-3 small text-success fw-semibold">
                            <?= number_format($m['ca_genere'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topMeds)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucune vente</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Liste des ventes -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
                <span><i class="bi bi-list-ul text-info me-2"></i>Detail des ventes</span>
                <span class="badge bg-secondary"><?= count($ventes) ?></span>
            </div>
            <div class="card-body p-0" style="max-height:350px;overflow-y:auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">Facture</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th class="text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ventes as $v): ?>
                    <tr>
                        <td class="ps-3">
                            <a href="<?= BASE_URL ?>/ventes/recu?id=<?= $v['id_vente'] ?>"
                               class="text-decoration-none small fw-semibold text-primary">
                                <?= htmlspecialchars($v['numero_facture']) ?>
                            </a>
                        </td>
                        <td class="small text-muted">
                            <?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($v['client_nom']) ?></td>
                        <td class="text-end pe-3 small fw-semibold">
                            <?= number_format($v['montant_total_ttc'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ventes)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Aucune vente sur cette periode.
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var parJour = <?= json_encode($parJour) ?>;
    var labels  = parJour.map(function(d) {
        var date = new Date(d.jour);
        return date.toLocaleDateString('fr-FR', {day:'2-digit', month:'short'});
    });
    var valeurs = parJour.map(function(d) { return parseFloat(d.ca_jour); });

    new Chart(document.getElementById('chartVentes'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'CA (F CFA)',
                data: valeurs,
                backgroundColor: 'rgba(46, 117, 182, 0.7)',
                borderColor: '#2E75B6',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.raw.toLocaleString('fr') + ' F CFA';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) {
                            return v.toLocaleString('fr') + ' F';
                        }
                    }
                }
            }
        }
    });
});
</script>
