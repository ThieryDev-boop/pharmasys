<?php // app/views/rapports/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-bar-chart-line text-primary me-2"></i>Rapports & Statistiques
    </h4>
    <span class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?>
    </span>
</div>

<!-- KPIs rapides -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-sun me-1"></i>CA Aujourd'hui</div>
            <div class="fw-bold fs-4 text-success">
                <?= number_format($stats_jour['ca_total'] ?? 0, 0, ',', ' ') ?> F
            </div>
            <div class="small text-muted"><?= $stats_jour['nb_ventes'] ?? 0 ?> vente(s)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-calendar-month me-1"></i>CA Ce mois</div>
            <div class="fw-bold fs-4 text-primary">
                <?= number_format($stats_mois['ca_total'] ?? 0, 0, ',', ' ') ?> F
            </div>
            <div class="small text-muted"><?= $stats_mois['nb_ventes'] ?? 0 ?> vente(s)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-box-seam me-1"></i>Valeur stock</div>
            <div class="fw-bold fs-4 text-info">
                <?= number_format($valeur_stock['valeur_vente'] ?? 0, 0, ',', ' ') ?> F
            </div>
            <div class="small text-muted"><?= $valeur_stock['nb_references'] ?? 0 ?> references</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Alertes stock</div>
            <div class="fw-bold fs-4 <?= count($alertes) > 0 ? 'text-danger' : 'text-success' ?>">
                <?= count($alertes) ?>
            </div>
            <div class="small text-muted"><?= count($peremptions) ?> peremption(s) < 30j</div>
        </div>
    </div>
</div>

<!-- Acces aux rapports -->
<div class="row g-3">

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-receipt text-success fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Rapport des ventes</h5>
                        <small class="text-muted">CA, top produits, ventes par caissier</small>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Analysez vos ventes sur n'importe quelle periode.
                    Export PDF et CSV disponibles.
                </p>
                <a href="<?= BASE_URL ?>/rapports/ventes" class="btn btn-success w-100">
                    <i class="bi bi-graph-up me-2"></i>Voir le rapport ventes
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-boxes text-info fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Etat du stock</h5>
                        <small class="text-muted">Ruptures, alertes, peremptions, valeur</small>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Visualisez l'etat complet de votre stock avec les alertes en cours.
                    Export PDF et CSV disponibles.
                </p>
                <a href="<?= BASE_URL ?>/rapports/stock" class="btn btn-info text-white w-100">
                    <i class="bi bi-clipboard-data me-2"></i>Voir l'etat du stock
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-truck text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Rapport fournisseurs</h5>
                        <small class="text-muted">Achats, commandes, fidelite</small>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Suivez vos depenses par fournisseur et l'historique des commandes.
                </p>
                <a href="<?= BASE_URL ?>/rapports/fournisseurs" class="btn btn-warning w-100">
                    <i class="bi bi-truck me-2"></i>Voir rapport fournisseurs
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Rapport clients</h5>
                        <small class="text-muted">Top clients, fidelite, panier moyen</small>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Identifiez vos meilleurs clients et analysez leurs habitudes d'achat.
                </p>
                <a href="<?= BASE_URL ?>/rapports/clients" class="btn btn-primary w-100">
                    <i class="bi bi-people me-2"></i>Voir rapport clients
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Alertes urgentes -->
<?php if (!empty($alertes) || !empty($peremptions)): ?>
<div class="row g-3 mt-1">
    <?php if (!empty($alertes)): ?>
    <div class="col-md-6">
        <div class="card border-danger border-0 shadow-sm">
            <div class="card-header bg-danger text-white py-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Ruptures et alertes stock (<?= count($alertes) ?>)
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <?php foreach (array_slice($alertes, 0, 5) as $a): ?>
                    <tr>
                        <td class="ps-3 small"><?= htmlspecialchars($a['nom_commercial']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $a['type_alerte'] === 'rupture' ? 'danger' : 'warning' ?>">
                                <?= $a['stock_total'] ?> / <?= $a['seuil_minimum'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($peremptions)): ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark py-2">
                <i class="bi bi-calendar-x-fill me-2"></i>
                Peremptions < 30 jours (<?= count($peremptions) ?>)
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <?php foreach (array_slice($peremptions, 0, 5) as $p): ?>
                    <tr>
                        <td class="ps-3 small"><?= htmlspecialchars($p['nom_commercial']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $p['jours_restants'] <= 7 ? 'danger' : 'warning' ?> text-dark">
                                <?= $p['jours_restants'] ?>j — <?= $p['numero_lot'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
