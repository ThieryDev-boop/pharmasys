<?php // app/views/dashboard/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark mb-0">
        <i class="bi bi-speedometer2 text-primary me-2"></i>Tableau de bord
    </h4>
    <small class="text-muted">
        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i') ?>
    </small>
</div>

<!-- Cartes statistiques -->
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2E75B6 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Ventes aujourd'hui</p>
                        <h3 class="fw-bold text-primary mb-0">
                            <?= htmlspecialchars($stats['ventes_jour'] ?? 0) ?>
                        </h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-cart3 fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">CA du jour</p>
                        <h3 class="fw-bold text-success mb-0">
                            <?= number_format($stats['ca_jour'] ?? 0, 0, ',', ' ') ?> F
                        </h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Alertes stock</p>
                        <h3 class="fw-bold text-warning mb-0">
                            <?= count($alertesStock) ?>
                        </h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Peremptions &lt; 90j</p>
                        <h3 class="fw-bold text-danger mb-0">
                            <?= count($alertesPeremption) ?>
                        </h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-x fs-4 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alertes actives -->
<?php if (!empty($alertesStock)): ?>
<div class="alert alert-warning border-0 shadow-sm mb-3">
    <h6 class="fw-bold mb-2">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Medicaments en rupture ou stock faible
    </h6>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach (array_slice($alertesStock, 0, 5) as $a): ?>
            <span class="badge bg-warning text-dark px-3 py-2">
                <?= htmlspecialchars($a['nom_commercial']) ?>
                &mdash; <?= $a['stock_total'] ?> / <?= $a['seuil_minimum'] ?>
            </span>
        <?php endforeach; ?>
        <?php if (count($alertesStock) > 5): ?>
            <a href="<?= BASE_URL ?>/medicaments?alerte=1" class="badge bg-secondary text-white px-3 py-2 text-decoration-none">
                +<?= count($alertesStock) - 5 ?> autres
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($alertesPeremption)): ?>
<div class="alert alert-danger border-0 shadow-sm mb-3">
    <h6 class="fw-bold mb-2">
        <i class="bi bi-calendar-x-fill me-2"></i>Lots proches de peremption (moins de 90 jours)
    </h6>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach (array_slice($alertesPeremption, 0, 5) as $a): ?>
            <span class="badge bg-danger px-3 py-2">
                <?= htmlspecialchars($a['nom_commercial']) ?>
                &mdash; <?= $a['jours_restants'] ?>j
                (lot <?= htmlspecialchars($a['numero_lot']) ?>)
            </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Raccourcis rapides -->
<div class="row g-3 mt-2">
    <div class="col-12">
        <h6 class="text-muted fw-semibold mb-3">
            <i class="bi bi-lightning me-1"></i>Acces rapide
        </h6>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/ventes/create" class="card border-0 shadow-sm text-decoration-none h-100 card-hover">
            <div class="card-body text-center py-4">
                <i class="bi bi-cart-plus fs-2 text-primary mb-2 d-block"></i>
                <span class="fw-semibold text-dark">Nouvelle vente</span>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/medicaments/create" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-plus-circle fs-2 text-success mb-2 d-block"></i>
                <span class="fw-semibold text-dark">Ajouter medicament</span>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/commandes/create" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-clipboard-plus fs-2 text-warning mb-2 d-block"></i>
                <span class="fw-semibold text-dark">Bon de commande</span>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/rapports" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-file-earmark-bar-graph fs-2 text-info mb-2 d-block"></i>
                <span class="fw-semibold text-dark">Rapports</span>
            </div>
        </a>
    </div>
</div>
