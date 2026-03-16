<?php // app/views/auth/login.php ?>

<h5 class="fw-bold mb-4 text-center text-dark">
    <i class="bi bi-lock me-2 text-primary"></i>Connexion
</h5>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/auth/login" autocomplete="off">
    <!-- Token CSRF (securite) -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Login -->
    <div class="mb-3">
        <label for="login" class="form-label fw-semibold text-secondary small">
            Identifiant
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-person"></i>
            </span>
            <input
                type="text"
                class="form-control"
                id="login"
                name="login"
                placeholder="Votre identifiant"
                value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                required
                autofocus
            >
        </div>
    </div>

    <!-- Mot de passe -->
    <div class="mb-4">
        <label for="password" class="form-label fw-semibold text-secondary small">
            Mot de passe
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-key"></i>
            </span>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Votre mot de passe"
                required
            >
            <button
                class="btn btn-outline-secondary"
                type="button"
                onclick="togglePassword()"
                tabindex="-1"
                title="Afficher/masquer"
            >
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>
    </div>

    <!-- Bouton connexion -->
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-login text-white">
            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
    </div>
</form>

<!-- Info compte par defaut -->
<div class="mt-4 p-3 bg-light rounded text-center">
    <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Compte admin par defaut :<br>
        Login : <strong>admin</strong> &nbsp;|&nbsp; Mot de passe : <strong>Admin@1234</strong>
    </small>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
