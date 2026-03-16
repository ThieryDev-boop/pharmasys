<?php // app/views/ventes/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-receipt text-primary me-2"></i>Historique des ventes
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <a href="<?= BASE_URL ?>/ventes/create" class="btn btn-primary">
        <i class="bi bi-cart-plus me-1"></i>Nouvelle vente
    </a>
</div>

<!-- Recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/ventes" class="d-flex gap-2">
            <input type="text" name="search" class="form-control"
                placeholder="Rechercher par N° facture ou client..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i></button>
            <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/ventes" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">N° Facture</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Caissier</th>
                        <th class="text-end">Total TTC</th>
                        <th class="text-end">Paye</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($ventes)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aucune vente enregistree.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventes as $v): ?>
                    <tr>
                        <td class="ps-3 fw-semibold text-primary">
                            <?= htmlspecialchars($v['numero_facture']) ?>
                        </td>
                        <td class="small text-muted">
                            <?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?>
                        </td>
                        <td>
                            <?= trim($v['client_nom']) ? htmlspecialchars(trim($v['client_nom'])) : '<span class="text-muted">Passage</span>' ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($v['caissier_nom']) ?></td>
                        <td class="text-end fw-semibold">
                            <?= number_format($v['montant_total_ttc'], 0, ',', ' ') ?> F
                        </td>
                        <td class="text-end">
                            <?= number_format($v['montant_paye'], 0, ',', ' ') ?> F
                        </td>
                        <td class="text-center pe-3">
                            <a href="<?= BASE_URL ?>/ventes/recu?id=<?= $v['id_vente'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Voir recu">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/ventes?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
