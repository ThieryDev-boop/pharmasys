<?php // app/views/lots/form.php ?>

<?php
function err(array $errors, string $key): string {
    return isset($errors[$key])
        ? '<div class="invalid-feedback d-block">' . htmlspecialchars($errors[$key]) . '</div>'
        : '';
}
function isInvalid(array $errors, string $key): string {
    return isset($errors[$key]) ? 'is-invalid' : '';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-plus-circle text-primary me-2"></i>Ajouter un lot
    </h4>
    <a href="<?= BASE_URL ?>/lots" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux lots
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-box-seam text-primary me-2"></i>Informations du lot
    </div>
    <div class="card-body">

        <form method="POST" action="<?= BASE_URL ?>/lots/create">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Medicament -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Medicament <span class="text-danger">*</span>
                </label>
                <?php if (isset($med) && $med): ?>
                    <!-- Medicament pre-selectionne depuis la fiche medicament -->
                    <input type="hidden" name="id_medicament" value="<?= $med['id_medicament'] ?>">
                    <div class="form-control bg-light">
                        <i class="bi bi-capsule text-primary me-2"></i>
                        <strong><?= htmlspecialchars($med['nom_commercial']) ?></strong>
                        <?= $med['dci'] ? '<span class="text-muted ms-2">(' . htmlspecialchars($med['dci']) . ')</span>' : '' ?>
                    </div>
                <?php else: ?>
                    <select name="id_medicament"
                        class="form-select <?= isInvalid($errors, 'id_medicament') ?>">
                        <option value="">-- Selectionnez un medicament --</option>
                        <?php foreach ($medicaments as $m): ?>
                        <option value="<?= $m['id_medicament'] ?>"
                            <?= (($old['id_medicament'] ?? 0) == $m['id_medicament']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nom_commercial']) ?>
                            <?= $m['dci'] ? '— ' . htmlspecialchars($m['dci']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?= err($errors, 'id_medicament') ?>
                <?php endif; ?>
            </div>

            <!-- Numero de lot -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Numero de lot <span class="text-danger">*</span>
                </label>
                <input type="text" name="numero_lot"
                    class="form-control <?= isInvalid($errors, 'numero_lot') ?>"
                    value="<?= htmlspecialchars($old['numero_lot'] ?? '') ?>"
                    placeholder="Ex: LOT-2024-001"
                    required>
                <small class="text-muted">Numero figurant sur l'emballage du fournisseur.</small>
                <?= err($errors, 'numero_lot') ?>
            </div>

            <!-- Dates -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de fabrication</label>
                    <input type="date" name="date_fabrication"
                        class="form-control"
                        value="<?= $old['date_fabrication'] ?? '' ?>"
                        max="<?= date('Y-m-d') ?>">
                    <small class="text-muted">Optionnelle.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Date de peremption <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="date_peremption"
                        class="form-control <?= isInvalid($errors, 'date_peremption') ?>"
                        value="<?= $old['date_peremption'] ?? '' ?>"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        required>
                    <?= err($errors, 'date_peremption') ?>
                </div>
            </div>

            <!-- Quantite et prix -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Quantite recue <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" name="quantite"
                            class="form-control <?= isInvalid($errors, 'quantite') ?>"
                            value="<?= $old['quantite'] ?? '' ?>"
                            min="1" placeholder="Ex: 100" required>
                        <span class="input-group-text">unites</span>
                    </div>
                    <?= err($errors, 'quantite') ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prix d'achat unitaire</label>
                    <div class="input-group">
                        <input type="number" name="prix_achat"
                            class="form-control"
                            value="<?= $old['prix_achat'] ?? '' ?>"
                            min="0" step="1" placeholder="0">
                        <span class="input-group-text">FCFA</span>
                    </div>
                    <small class="text-muted">Optionnel.</small>
                </div>
            </div>

            <!-- Info FEFO -->
            <div class="alert alert-info py-2 mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <small>
                    <strong>Regle FEFO appliquee automatiquement :</strong>
                    lors des ventes, les lots avec la date de peremption la plus proche
                    seront vendus en priorite.
                </small>
            </div>

            <!-- Boutons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter le lot
                </button>
                <a href="<?= BASE_URL ?>/lots" class="btn btn-outline-secondary px-4">
                    Annuler
                </a>
            </div>

        </form>
    </div>
</div>
</div>
</div>
