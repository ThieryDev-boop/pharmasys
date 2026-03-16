<?php // app/views/rapports/fournisseurs.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-truck text-warning me-2"></i>Rapport fournisseurs
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/rapports/fournisseurs?export=pdf" target="_blank"
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
                    <th>Fournisseur</th>
                    <th>Contact</th>
                    <th class="text-center">Nb commandes</th>
                    <th class="text-end">Total achats</th>
                    <th class="text-center pe-3">Derniere commande</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($statsFournisseurs as $i => $f): ?>
            <tr>
                <td class="ps-3 text-muted small"><?= $i + 1 ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/fournisseurs/detail?id=<?= $f['id_fournisseur'] ?? '' ?>"
                       class="fw-semibold text-decoration-none">
                        <?= htmlspecialchars($f['raison_sociale']) ?>
                    </a>
                </td>
                <td class="small text-muted">
                    <?= $f['telephone'] ? htmlspecialchars($f['telephone']) : '—' ?>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3">
                        <?= $f['nb_commandes'] ?>
                    </span>
                </td>
                <td class="text-end fw-semibold">
                    <?= number_format($f['total_achats'], 0, ',', ' ') ?> F
                </td>
                <td class="text-center small text-muted pe-3">
                    <?= $f['derniere_commande']
                        ? date('d/m/Y', strtotime($f['derniere_commande']))
                        : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($statsFournisseurs)): ?>
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">Aucune donnee</td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
