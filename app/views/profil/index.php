<?php // app/views/profil/index.php ?>

<?php
$roleConfig = [
    'administrateur' => ['color' => 'danger',  'icon' => 'bi-shield-fill',   'label' => 'Administrateur'],
    'pharmacien'     => ['color' => 'primary',  'icon' => 'bi-capsule',       'label' => 'Pharmacien'],
    'caissier'       => ['color' => 'success',  'icon' => 'bi-cash-register', 'label' => 'Caissier'],
];
$rc = $roleConfig[$utilisateur['role']] ?? ['color' => 'secondary', 'icon' => 'bi-person', 'label' => $utilisateur['role']];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-circle text-primary me-2"></i>Mon profil
    </h4>
</div>

<!-- Messages succes -->
<?php if ($success === 'infos'): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    Vos informations ont ete mises a jour.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php elseif ($success === 'mdp'): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    Mot de passe modifie avec succes.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Erreurs -->
<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- Carte identite -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <!-- Avatar grand -->
            <div class="mx-auto mb-3 rounded-circle bg-<?= $rc['color'] ?> bg-opacity-15
                         text-<?= $rc['color'] ?> fw-bold d-flex align-items-center
                         justify-content-center"
                 style="width:90px;height:90px;font-size:2rem;">
                <?= strtoupper(
                    substr($utilisateur['nom'], 0, 1) .
                    substr($utilisateur['prenom'] ?? '', 0, 1)
                ) ?>
            </div>

            <h5 class="fw-bold mb-1">
                <?= htmlspecialchars($utilisateur['nom'] . ' ' . ($utilisateur['prenom'] ?? '')) ?>
            </h5>
            <p class="text-muted small mb-2">
                        </p>

            <span class="badge bg-<?= $rc['color'] ?> px-3 py-2 mb-3">
                <i class="bi <?= $rc['icon'] ?> me-1"></i><?= $rc['label'] ?>
            </span>

            <hr>

            <div class="text-start small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Compte cree le</span>
                    <span class="fw-semibold">
                        <?= date('d/m/Y', strtotime($utilisateur['date_creation'])) ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Derniere connexion</span>
                    <span class="fw-semibold">
                        <?= $utilisateur['derniere_connexion']
                            ? date('d/m/Y H:i', strtotime($utilisateur['derniere_connexion']))
                            : 'N/A' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Statut</span>
                    <span class="badge bg-success">Actif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaires -->
    <div class="col-md-8">

        <!-- Section : Informations personnelles -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-person text-primary me-2"></i>Informations personnelles
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/profil/modifier-infos">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom" class="form-control"
                                value="<?= htmlspecialchars($utilisateur['nom']) ?>"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prenom</label>
                            <input type="text" name="prenom" class="form-control"
                                value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control"
                                    value="">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role</label>
                            <input type="text" class="form-control bg-light"
                                value="<?= $rc['label'] ?>" disabled>
                            <small class="text-muted">Le role est gere par l'administrateur.</small>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Section : Changer le mot de passe -->
        <div class="card border-0 shadow-sm" id="section-mdp">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-lock text-warning me-2"></i>Changer le mot de passe
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/profil/changer-mdp">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Mot de passe actuel <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="mdp_actuel"
                                    class="form-control" id="mdpActuel"
                                    placeholder="Votre mot de passe actuel"
                                    autocomplete="current-password" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="toggleVisi('mdpActuel', 'oeilActuel')">
                                    <i class="bi bi-eye" id="oeilActuel"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nouveau mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="mdp_nouveau"
                                    class="form-control" id="mdpNouveau"
                                    placeholder="Minimum 6 caracteres"
                                    autocomplete="new-password" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="toggleVisi('mdpNouveau', 'oeilNouveau')">
                                    <i class="bi bi-eye" id="oeilNouveau"></i>
                                </button>
                            </div>
                            <!-- Barre de force -->
                            <div class="progress mt-1" style="height:4px;">
                                <div class="progress-bar" id="forceBar" style="width:0%"></div>
                            </div>
                            <small id="forceLabel" class="text-muted"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Confirmer <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="mdp_confirmation"
                                    class="form-control" id="mdpConfirm"
                                    placeholder="Retapez le nouveau mot de passe"
                                    autocomplete="new-password" required>
                            </div>
                            <small id="confirmLabel" class="mt-1 d-block"></small>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Minimum 6 caracteres. Utilisez majuscules, chiffres et symboles.
                        </small>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-lock me-2"></i>Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Visibilite mot de passe
    window.toggleVisi = function(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type    = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type    = 'password';
            icon.className = 'bi bi-eye';
        }
    };

    // Force du mot de passe
    var mdpNouveau = document.getElementById('mdpNouveau');
    var forceBar   = document.getElementById('forceBar');
    var forceLabel = document.getElementById('forceLabel');

    mdpNouveau.addEventListener('input', function() {
        var mdp   = this.value;
        var force = 0;
        if (mdp.length >= 6)              force += 25;
        if (mdp.length >= 10)             force += 25;
        if (/[A-Z]/.test(mdp))            force += 25;
        if (/[0-9!@#$%^&*]/.test(mdp))    force += 25;

        var niveaux = [
            { seuil: 25,  color: 'bg-danger',  label: 'Faible' },
            { seuil: 50,  color: 'bg-warning',  label: 'Moyen' },
            { seuil: 75,  color: 'bg-info',     label: 'Bon' },
            { seuil: 100, color: 'bg-success',  label: 'Excellent' },
        ];
        var niveau = niveaux.find(function(n) { return force <= n.seuil; }) || niveaux[3];
        forceBar.className  = 'progress-bar ' + niveau.color;
        forceBar.style.width = force + '%';
        forceLabel.textContent = mdp.length > 0 ? niveau.label : '';
        verifierConfirm();
    });

    // Verification confirmation
    var mdpConfirm   = document.getElementById('mdpConfirm');
    var confirmLabel = document.getElementById('confirmLabel');

    mdpConfirm.addEventListener('input', verifierConfirm);

    function verifierConfirm() {
        var mdp     = mdpNouveau.value;
        var confirm = mdpConfirm.value;
        if (!confirm) { confirmLabel.textContent = ''; return; }
        if (mdp === confirm) {
            confirmLabel.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Identique</span>';
        } else {
            confirmLabel.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Differents</span>';
        }
    }
});
</script>
