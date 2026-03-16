<?php // app/views/clients/form.php ?>

<?php
$estEdition = isset($client) && !empty($client);
$titre      = $estEdition ? 'Modifier le client' : 'Nouveau client';

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
        <i class="bi bi-person-<?= $estEdition ? 'gear' : 'plus' ?> text-primary me-2"></i>
        <?= $titre ?>
    </h4>
    <a href="<?= BASE_URL ?>/clients" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-person-vcard text-primary me-2"></i>Fiche client
    </div>
    <div class="card-body">

        <form method="POST"
              action="<?= BASE_URL ?>/clients/<?= $estEdition ? 'edit' : 'create' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <?php if ($estEdition): ?>
            <input type="hidden" name="id_client" value="<?= $client['id_client'] ?>">
            <?php endif; ?>

            <!-- Nom -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Nom <span class="text-danger">*</span>
                </label>
                <input type="text" name="nom"
                    class="form-control <?= isInvalid($errors, 'nom') ?>"
                    value="<?= htmlspecialchars($old['nom'] ?? '') ?>"
                    placeholder="Nom de famille"
                    required autofocus>
                <?= err($errors, 'nom') ?>
            </div>

            <!-- Prenom -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Prenom</label>
                <input type="text" name="prenom"
                    class="form-control"
                    value="<?= htmlspecialchars($old['prenom'] ?? '') ?>"
                    placeholder="Prenom (optionnel)">
            </div>

            <!-- Telephone -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Telephone</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-telephone"></i>
                    </span>
                    <input type="text" name="telephone"
                        class="form-control <?= isInvalid($errors, 'telephone') ?>"
                        value="<?= htmlspecialchars($old['telephone'] ?? '') ?>"
                        placeholder="Ex: +237 6XX XXX XXX">
                </div>
                <?= err($errors, 'telephone') ?>
            </div>

            <!-- Adresse -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Adresse</label>
                <textarea name="adresse" class="form-control" rows="2"
                    placeholder="Quartier, ville (optionnel)"><?= htmlspecialchars($old['adresse'] ?? '') ?></textarea>
            </div>

            <!-- Boutons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-<?= $estEdition ? 'check-lg' : 'person-plus' ?> me-2"></i>
                    <?= $estEdition ? 'Enregistrer' : 'Creer le client' ?>
                </button>
                <a href="<?= BASE_URL ?>/clients" class="btn btn-outline-secondary px-4">
                    Annuler
                </a>
            </div>
        </form>

    </div>
</div>

<?php if ($estEdition): ?>
<!-- Stats rapides -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="bi bi-clock me-1"></i>
                Client depuis le <?= date('d/m/Y', strtotime($client['date_creation'])) ?>
            </span>
            <a href="<?= BASE_URL ?>/clients/historique?id=<?= $client['id_client'] ?>"
               class="btn btn-sm btn-outline-info">
                <i class="bi bi-clock-history me-1"></i>Voir historique
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

</div>
</div>
