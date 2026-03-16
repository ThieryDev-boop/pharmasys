<?php // app/views/medicaments/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-capsule text-primary me-2"></i>Medicaments
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <?php if (Auth::hasRole('administrateur', 'pharmacien')): ?>
    <a href="<?= BASE_URL ?>/medicaments/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nouveau medicament
    </a>
    <?php endif; ?>
</div>

<!-- Messages succes -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?php
    $msgs = [
        'cree'    => 'Medicament cree avec succes.',
        'modifie' => 'Medicament modifie avec succes.',
        'archive' => 'Medicament archive avec succes.',
    ];
    echo $msgs[$_GET['success']] ?? 'Operation reussie.';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Barre de recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/medicaments" class="d-flex gap-2">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Rechercher par nom, DCI ou code-barres..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/medicaments" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
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
                        <th class="ps-3">Nom commercial</th>
                        <th>DCI</th>
                        <th>Forme / Dosage</th>
                        <th>Categorie</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">Prix vente</th>
                        <th class="text-center">Ord.</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($medicaments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            <?= $search ? 'Aucun resultat pour "' . htmlspecialchars($search) . '"' : 'Aucun medicament enregistre.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($medicaments as $med): ?>
                    <?php
                        $stockTotal = (int)$med['stock_total'];
                        $seuil      = (int)$med['seuil_minimum'];
                        if ($stockTotal == 0) {
                            $stockClass = 'danger';
                            $stockIcon  = 'bi-x-circle-fill';
                        } elseif ($stockTotal <= $seuil) {
                            $stockClass = 'warning';
                            $stockIcon  = 'bi-exclamation-triangle-fill';
                        } else {
                            $stockClass = 'success';
                            $stockIcon  = 'bi-check-circle-fill';
                        }
                    ?>
                    <tr>
                        <td class="ps-3 fw-semibold">
                            <?= htmlspecialchars($med['nom_commercial']) ?>
                        </td>
                        <td class="text-muted small">
                            <?= htmlspecialchars($med['dci'] ?? '-') ?>
                        </td>
                        <td class="small">
                            <?= htmlspecialchars($med['forme_galenique'] ?? '') ?>
                            <?= $med['dosage'] ? '<span class="text-muted">— ' . htmlspecialchars($med['dosage']) . '</span>' : '' ?>
                        </td>
                        <td>
                            <?php if ($med['categorie_nom']): ?>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($med['categorie_nom']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $stockClass ?> bg-opacity-15 text-<?= $stockClass ?> border border-<?= $stockClass ?> px-3">
                                <i class="bi <?= $stockIcon ?> me-1"></i><?= $stockTotal ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= number_format($med['prix_vente'], 0, ',', ' ') ?> F
                        </td>
                        <td class="text-center">
                            <?php if ($med['ordonnance_requise']): ?>
                            <span class="badge bg-danger" title="Ordonnance obligatoire">
                                <i class="bi bi-file-medical"></i>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3">
                            <?php if (Auth::hasRole('administrateur', 'pharmacien')): ?>
                            <a href="<?= BASE_URL ?>/medicaments/edit?id=<?= $med['id_medicament'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Bouton archiver -->
                            <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                title="Archiver"
                                onclick="confirmerArchivage(<?= $med['id_medicament'] ?>, '<?= htmlspecialchars(addslashes($med['nom_commercial'])) ?>')">
                                <i class="bi bi-archive"></i>
                            </button>
                            <?php endif; ?>
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
            <a class="page-link" href="<?= BASE_URL ?>/medicaments?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal confirmation archivage -->
<div class="modal fade" id="modalArchiver" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-archive me-2"></i>Confirmer l'archivage
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Voulez-vous archiver le medicament <strong id="nomMedicament"></strong> ?
                <br><small class="text-muted">Il ne sera plus visible mais les donnees historiques seront conservees.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/medicaments/delete" id="formArchiver">
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    <input type="hidden" name="id_medicament" id="idMedicamentArchiver">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-archive me-1"></i>Archiver
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmerArchivage(id, nom) {
    document.getElementById('idMedicamentArchiver').value = id;
    document.getElementById('nomMedicament').textContent = nom;
    new bootstrap.Modal(document.getElementById('modalArchiver')).show();
}
</script>
