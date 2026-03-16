<?php // app/views/lots/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-boxes text-primary me-2"></i>Gestion des lots
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <a href="<?= BASE_URL ?>/lots/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Ajouter un lot
    </a>
</div>

<!-- Message succes -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>Lot ajoute avec succes.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Alertes peremption -->
<?php if (!empty($alertes)): ?>
<div class="alert alert-danger border-0 shadow-sm mb-3">
    <h6 class="fw-bold mb-2">
        <i class="bi bi-calendar-x-fill me-2"></i>
        <?= count($alertes) ?> lot(s) proches de peremption (moins de 90 jours)
    </h6>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach (array_slice($alertes, 0, 6) as $a): ?>
        <?php $couleur = $a['jours_restants'] <= 30 ? 'danger' : 'warning'; ?>
        <span class="badge bg-<?= $couleur ?> px-3 py-2">
            <?= htmlspecialchars($a['nom_commercial']) ?>
            — Lot <?= htmlspecialchars($a['numero_lot']) ?>
            — <?= $a['jours_restants'] ?>j
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filtres + recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/lots" class="d-flex flex-wrap gap-2 align-items-center">

            <!-- Filtres rapides -->
            <div class="btn-group" role="group">
                <?php
                $filtres = [
                    'tous'   => ['label' => 'Tous',         'color' => 'secondary'],
                    'actif'  => ['label' => 'En stock',     'color' => 'success'],
                    'alerte' => ['label' => 'Alerte 90j',   'color' => 'warning'],
                    'perime' => ['label' => 'Perimes',      'color' => 'danger'],
                    'epuise' => ['label' => 'Epuises',      'color' => 'dark'],
                ];
                foreach ($filtres as $key => $f):
                    $actif = ($filtre === $key);
                ?>
                <a href="<?= BASE_URL ?>/lots?filtre=<?= $key ?>&search=<?= urlencode($search) ?>"
                   class="btn btn-sm btn-<?= $actif ? '' : 'outline-' ?><?= $f['color'] ?>">
                    <?= $f['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Barre de recherche -->
            <div class="d-flex gap-2 ms-auto">
                <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre) ?>">
                <input type="text" name="search" class="form-control form-control-sm"
                    style="width:220px;"
                    placeholder="Medicament ou N° lot..."
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search): ?>
                <a href="<?= BASE_URL ?>/lots?filtre=<?= $filtre ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des lots -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Medicament</th>
                        <th>N° Lot</th>
                        <th class="text-center">Date fabrication</th>
                        <th class="text-center">Date peremption</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Qte initiale</th>
                        <th class="text-center">Qte restante</th>
                        <th class="text-end pe-3">Prix achat</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lots)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aucun lot trouve.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lots as $lot): ?>
                    <?php
                        $jours = (int)$lot['jours_restants'];
                        if ($lot['date_peremption'] < date('Y-m-d')) {
                            $statutClass = 'danger';
                            $statutLabel = 'Perime';
                            $statutIcon  = 'bi-x-circle-fill';
                        } elseif ($jours <= 30) {
                            $statutClass = 'danger';
                            $statutLabel = $jours . 'j restants';
                            $statutIcon  = 'bi-exclamation-circle-fill';
                        } elseif ($jours <= 90) {
                            $statutClass = 'warning';
                            $statutLabel = $jours . 'j restants';
                            $statutIcon  = 'bi-exclamation-triangle-fill';
                        } elseif ($lot['quantite_restante'] == 0) {
                            $statutClass = 'secondary';
                            $statutLabel = 'Epuise';
                            $statutIcon  = 'bi-dash-circle-fill';
                        } else {
                            $statutClass = 'success';
                            $statutLabel = 'OK';
                            $statutIcon  = 'bi-check-circle-fill';
                        }
                        $pctRestant = $lot['quantite_initiale'] > 0
                            ? round(($lot['quantite_restante'] / $lot['quantite_initiale']) * 100)
                            : 0;
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold"><?= htmlspecialchars($lot['nom_commercial']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($lot['dci'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold">
                                <?= htmlspecialchars($lot['numero_lot']) ?>
                            </span>
                        </td>
                        <td class="text-center small text-muted">
                            <?= $lot['date_fabrication']
                                ? date('d/m/Y', strtotime($lot['date_fabrication']))
                                : '—' ?>
                        </td>
                        <td class="text-center small fw-semibold">
                            <?= date('d/m/Y', strtotime($lot['date_peremption'])) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $statutClass ?>">
                                <i class="bi <?= $statutIcon ?> me-1"></i><?= $statutLabel ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $lot['quantite_initiale'] ?></td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress" style="width:60px; height:6px;">
                                    <div class="progress-bar bg-<?= $statutClass ?>"
                                         style="width:<?= $pctRestant ?>%"></div>
                                </div>
                                <span class="fw-semibold"><?= $lot['quantite_restante'] ?></span>
                            </div>
                        </td>
                        <td class="text-end pe-3 small">
                            <?= $lot['prix_achat'] > 0
                                ? number_format($lot['prix_achat'], 0, ',', ' ') . ' F'
                                : '—' ?>
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
            <a class="page-link"
               href="<?= BASE_URL ?>/lots?page=<?= $i ?>&filtre=<?= $filtre ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
