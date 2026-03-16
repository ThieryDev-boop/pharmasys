<?php // app/views/fournisseurs/detail.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-truck text-primary me-2"></i>
        <?= htmlspecialchars($fournisseur['raison_sociale']) ?>
    </h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="ouvrirEditionDepuisDetail()">
            <i class="bi bi-pencil me-1"></i>Modifier
        </button>
        <a href="<?= BASE_URL ?>/fournisseurs" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- Infos fournisseur -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-building text-primary me-2"></i>Informations
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small" style="width:130px;">Raison sociale</td>
                        <td class="fw-semibold"><?= htmlspecialchars($fournisseur['raison_sociale']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Contact</td>
                        <td><?= $fournisseur['contact'] ? htmlspecialchars($fournisseur['contact']) : '<span class="text-muted">—</span>' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Telephone</td>
                        <td>
                            <?php if ($fournisseur['telephone']): ?>
                            <a href="tel:<?= htmlspecialchars($fournisseur['telephone']) ?>"
                               class="text-decoration-none">
                                <i class="bi bi-telephone me-1"></i>
                                <?= htmlspecialchars($fournisseur['telephone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Email</td>
                        <td>
                            <?php if ($fournisseur['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($fournisseur['email']) ?>"
                               class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i>
                                <?= htmlspecialchars($fournisseur['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Adresse</td>
                        <td><?= $fournisseur['adresse'] ? htmlspecialchars($fournisseur['adresse']) : '<span class="text-muted">—</span>' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Partenaire depuis</td>
                        <td><?= date('d/m/Y', strtotime($fournisseur['date_creation'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="col-md-7">
        <div class="row g-3">
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-primary"><?= $stats['nb_commandes'] ?? 0 ?></div>
                    <small class="text-muted">Commandes passees</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-4 text-success">
                        <?= number_format($stats['total_commandes'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                    <small class="text-muted">Total achats</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-5 text-info">
                        <?= number_format($stats['montant_moyen'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                    <small class="text-muted">Commande moyenne</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-6 text-warning">
                        <?= $stats['derniere_commande']
                            ? date('d/m/Y', strtotime($stats['derniere_commande']))
                            : '—' ?>
                    </div>
                    <small class="text-muted">Derniere commande</small>
                </div>
            </div>
        </div>

        <!-- Bouton nouvelle commande -->
        <div class="mt-3">
            <a href="<?= BASE_URL ?>/commandes/create?id_fournisseur=<?= $fournisseur['id_fournisseur'] ?>"
               class="btn btn-warning w-100">
                <i class="bi bi-clipboard-plus me-2"></i>Passer une nouvelle commande
            </a>
        </div>
    </div>

</div>

<!-- Historique commandes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-clipboard-check text-warning me-2"></i>Historique des commandes
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">N° Commande</th>
                        <th>Date</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end">Montant</th>
                        <th>Cree par</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($commandes)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aucune commande passee.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $statutColors = [
                        'brouillon' => 'secondary',
                        'envoyee'   => 'primary',
                        'recue'     => 'success',
                        'annulee'   => 'danger',
                    ];
                    foreach ($commandes as $cmd):
                        $color = $statutColors[$cmd['statut']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="ps-3 fw-semibold text-primary">
                            <?= htmlspecialchars($cmd['numero_commande']) ?>
                        </td>
                        <td class="small">
                            <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $color ?> text-capitalize">
                                <?= htmlspecialchars($cmd['statut']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= $cmd['montant_total'] > 0
                                ? number_format($cmd['montant_total'], 0, ',', ' ') . ' F'
                                : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($cmd['createur_nom']) ?>
                        </td>
                        <td class="text-center pe-3">
                            <a href="<?= BASE_URL ?>/commandes/detail?id=<?= $cmd['id_commande'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
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

<!-- Modale edition (depuis la page detail) -->
<div class="modal fade" id="modalEdition" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Modifier le fournisseur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/fournisseurs/edit">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_fournisseur" value="<?= $fournisseur['id_fournisseur'] ?>">
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                            <input type="text" name="raison_sociale" class="form-control"
                                value="<?= htmlspecialchars($fournisseur['raison_sociale']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact</label>
                            <input type="text" name="contact" class="form-control"
                                value="<?= htmlspecialchars($fournisseur['contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telephone</label>
                            <input type="text" name="telephone" class="form-control"
                                value="<?= htmlspecialchars($fournisseur['telephone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($fournisseur['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Adresse</label>
                            <input type="text" name="adresse" class="form-control"
                                value="<?= htmlspecialchars($fournisseur['adresse'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-lg me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function ouvrirEditionDepuisDetail() {
    new bootstrap.Modal(document.getElementById('modalEdition')).show();
}
</script>
