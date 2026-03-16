<?php // app/views/rapports/clients.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people text-primary me-2"></i>Rapport clients — Top 20
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/rapports/clients?export=pdf" target="_blank"
           class="btn btn-sm btn-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="<?= BASE_URL ?>/rapports" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Client</th>
                    <th>Telephone</th>
                    <th class="text-center">Nb achats</th>
                    <th class="text-end">Total depense</th>
                    <th class="text-end">Panier moyen</th>
                    <th class="text-center pe-3">Dernier achat</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($topClients as $i => $c): ?>
            <tr>
                <td class="ps-3">
                    <?php if ($i < 3): ?>
                    <span class="badge bg-<?= ['warning', 'secondary', 'danger'][$i] ?> rounded-circle p-2">
                        <?= $i + 1 ?>
                    </span>
                    <?php else: ?>
                    <span class="text-muted small"><?= $i + 1 ?></span>
                    <?php endif; ?>
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($c['client_nom']) ?></td>
                <td class="small text-muted"><?= $c['telephone'] ? htmlspecialchars($c['telephone']) : '—' ?></td>
                <td class="text-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3">
                        <?= $c['nb_achats'] ?>
                    </span>
                </td>
                <td class="text-end fw-bold text-success">
                    <?= number_format($c['total_depense'], 0, ',', ' ') ?> F
                </td>
                <td class="text-end text-muted small">
                    <?= number_format($c['panier_moyen'], 0, ',', ' ') ?> F
                </td>
                <td class="text-center small text-muted pe-3">
                    <?= date('d/m/Y', strtotime($c['dernier_achat'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topClients)): ?>
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">Aucune donnee</td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
