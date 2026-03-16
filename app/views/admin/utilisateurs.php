<?php // app/views/admin/utilisateurs.php ?>

<?php
$ouvrirModal = !empty($_GET['erreur']) && !empty($formOld);

// Stats par role
$statsRoles = [];
foreach ($stats as $s) {
    $statsRoles[$s['role']] = $s;
}

$roleConfig = [
    'administrateur' => ['color' => 'danger',  'icon' => 'bi-shield-fill',    'label' => 'Administrateur'],
    'pharmacien'     => ['color' => 'primary',  'icon' => 'bi-capsule',        'label' => 'Pharmacien'],
    'caissier'       => ['color' => 'success',  'icon' => 'bi-cash-register',  'label' => 'Caissier'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people-fill text-primary me-2"></i>Gestion des utilisateurs
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <button type="button" class="btn btn-primary"
        data-bs-toggle="modal" data-bs-target="#modalUtilisateur">
        <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
    </button>
</div>

<!-- Messages -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?php
    $msgs = [
        'cree'    => 'Utilisateur cree avec succes.',
        'modifie' => 'Utilisateur modifie avec succes.',
        'reinit'  => 'Mot de passe reinitialise.',
    ];
    echo $msgs[$_GET['success']] ?? 'Operation reussie.';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($_GET['erreur']) && $_GET['erreur'] === 'selfdeactivate'): ?>
<div class="alert alert-warning alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Vous ne pouvez pas desactiver votre propre compte.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Mot de passe temporaire -->
<?php if (!empty($_SESSION['mdp_temp'])): ?>
<div class="alert alert-info alert-dismissible fade show">
    <i class="bi bi-key me-2"></i>
    Mot de passe temporaire genere :
    <strong class="font-monospace fs-5 ms-2"><?= htmlspecialchars($_SESSION['mdp_temp']) ?></strong>
    <br><small class="text-muted">Communiquez ce mot de passe a l'utilisateur. Il devra le changer.</small>
    <button type="button" class="btn-close" data-bs-dismiss="alert"
        onclick="clearMdpTemp()"></button>
</div>
<?php unset($_SESSION['mdp_temp'], $_SESSION['mdp_temp_user']); ?>
<?php endif; ?>

<!-- Stats par role -->
<div class="row g-3 mb-4">
    <?php foreach ($roleConfig as $role => $rc): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-<?= $rc['color'] ?> bg-opacity-10 p-3">
                    <i class="bi <?= $rc['icon'] ?> text-<?= $rc['color'] ?> fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-<?= $rc['color'] ?>">
                        <?= $statsRoles[$role]['nb_actifs'] ?? 0 ?>
                        <small class="text-muted fs-6">/ <?= $statsRoles[$role]['nb_total'] ?? 0 ?></small>
                    </div>
                    <div class="small text-muted"><?= $rc['label'] ?>s actifs</div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/admin/utilisateurs" class="d-flex gap-2">
            <input type="text" name="search" class="form-control"
                placeholder="Rechercher par nom, prenom ou email..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/admin/utilisateurs" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Utilisateur</th>
                    <th>Email</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Derniere connexion</th>
                    <th class="text-center pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($utilisateurs)): ?>
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                    Aucun utilisateur trouve.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($utilisateurs as $u):
                $rc = $roleConfig[$u['role']] ?? ['color' => 'secondary', 'icon' => 'bi-person', 'label' => $u['role']];
                $estMoi = ($u['id_utilisateur'] == Auth::userId());
            ?>
            <tr class="<?= !$u['actif'] ? 'opacity-50' : '' ?>">
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Avatar initiales -->
                        <div class="rounded-circle bg-<?= $rc['color'] ?> bg-opacity-15
                                    text-<?= $rc['color'] ?> fw-bold d-flex align-items-center
                                    justify-content-center"
                             style="width:38px;height:38px;font-size:0.85rem;flex-shrink:0;">
                            <?= strtoupper(substr($u['nom'], 0, 1) . substr($u['prenom'] ?? '', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold">
                                <?= htmlspecialchars($u['nom'] . ' ' . ($u['prenom'] ?? '')) ?>
                                <?php if ($estMoi): ?>
                                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Vous</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">
                                Depuis <?= date('d/m/Y', strtotime($u['date_creation'])) ?>
                            </small>
                        </div>
                    </div>
                </td> 
                <td class="small">
                    <p>service emaile non disponible pour le moment</p>
                </td>
                <td class="text-center">
                    <span class="badge bg-<?= $rc['color'] ?>">
                        <i class="bi <?= $rc['icon'] ?> me-1"></i><?= $rc['label'] ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if ($u['actif']): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i>Actif
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                        <i class="bi bi-circle me-1" style="font-size:0.5rem"></i>Inactif
                    </span>
                    <?php endif; ?>
                </td>
                <td class="text-center small text-muted">
                    <?= $u['derniere_connexion']
                        ? date('d/m/Y H:i', strtotime($u['derniere_connexion']))
                        : '<span class="text-muted">Jamais</span>' ?>
                </td>
                <td class="text-center pe-3">
                    <!-- Modifier -->
                    <button type="button"
                        class="btn btn-sm btn-outline-primary me-1"
                        title="Modifier"
                        onclick="ouvrirEdition(
                            <?= $u['id_utilisateur'] ?>,
                            '<?= htmlspecialchars(addslashes($u['nom'])) ?>',
                            '<?= htmlspecialchars(addslashes($u['prenom'] ?? '')) ?>',
                            '<?= htmlspecialchars(addslashes($u['email'] ?? '')) ?>',
                            '<?= $u['role'] ?>',
                            '<?= htmlspecialchars(addslashes($u['login'])) ?>'
                        )">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!-- Reinit mdp -->
                    <button type="button"
                        class="btn btn-sm btn-outline-warning me-1"
                        title="Reinitialiser le mot de passe"
                        onclick="confirmerReinitMdp(<?= $u['id_utilisateur'] ?>,
                            '<?= htmlspecialchars(addslashes($u['nom'] . ' ' . ($u['prenom'] ?? ''))) ?>')">
                        <i class="bi bi-key"></i>
                    </button>
                    <!-- Activer / Desactiver -->
                    <?php if (!$estMoi): ?>
                    <button type="button"
                        class="btn btn-sm btn-outline-<?= $u['actif'] ? 'danger' : 'success' ?>"
                        title="<?= $u['actif'] ? 'Desactiver' : 'Activer' ?>"
                        onclick="confirmerToggle(
                            <?= $u['id_utilisateur'] ?>,
                            '<?= htmlspecialchars(addslashes($u['nom'])) ?>',
                            <?= $u['actif'] ? 'true' : 'false' ?>
                        )">
                        <i class="bi bi-<?= $u['actif'] ? 'person-dash' : 'person-check' ?>"></i>
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link"
               href="<?= BASE_URL ?>/admin/utilisateurs?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>


<!-- ============================================================ -->
<!-- MODALE CREATION / EDITION                                     -->
<!-- ============================================================ -->
<div class="modal fade" id="modalUtilisateur" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus text-primary me-2" id="modalIcon"></i>
                    <span id="modalTitre">Nouvel utilisateur</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="formUtilisateur">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_utilisateur" id="inputIdUtilisateur" value="">

                <div class="modal-body pt-3">

                    <!-- Erreurs -->
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom" id="inputNom"
                                class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($formOld['nom'] ?? '') ?>"
                                placeholder="Dupont" required>
                            <?php if (isset($errors['nom'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['nom']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prenom</label>
                            <input type="text" name="prenom" id="inputPrenom"
                                class="form-control"
                                value="<?= htmlspecialchars($formOld['prenom'] ?? '') ?>"
                                placeholder="Jean">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Login <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="login" id="inputLogin"
                                    class="form-control"
                                    value="<?= htmlspecialchars($formOld['login'] ?? '') ?>"
                                    placeholder="jean.dupont"
                                    autocomplete="off">
                            </div>
                            <small class="text-muted" id="loginAutoInfo">
                                <i class="bi bi-magic me-1"></i>Auto-genere si vide
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="inputEmail"
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formOld['email'] ?? '') ?>"
                                    placeholder="jean.dupont@pharmacie.com">
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="inputRole" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <option value="caissier"       <?= ($formOld['role'] ?? '') === 'caissier'       ? 'selected' : '' ?>>Caissier</option>
                                <option value="pharmacien"     <?= ($formOld['role'] ?? '') === 'pharmacien'     ? 'selected' : '' ?>>Pharmacien</option>
                                <option value="administrateur" <?= ($formOld['role'] ?? '') === 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Mot de passe
                                <span id="mdpObligatoire" class="text-danger">*</span>
                                <span id="mdpOptionnel" class="text-muted small d-none">
                                    (laisser vide = inchange)
                                </span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="mot_de_passe" id="inputMdp"
                                    class="form-control <?= isset($errors['mot_de_passe']) ? 'is-invalid' : '' ?>"
                                    placeholder="Minimum 6 caracteres"
                                    autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="toggleMdpVisibilite()">
                                    <i class="bi bi-eye" id="iconOeil"></i>
                                </button>
                                <?php if (isset($errors['mot_de_passe'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['mot_de_passe']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="progress mt-1" style="height:4px;" id="forceBarreContainer">
                                <div class="progress-bar" id="forceBarre" style="width:0%"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Confirmer</label>
                            <input type="password" name="confirmation_mdp" id="inputConfirm"
                                class="form-control"
                                placeholder="Retapez le mot de passe"
                                autocomplete="new-password">
                            <div id="msgConfirm" class="small mt-1"></div>
                        </div>
                    </div>

                    <!-- Info roles -->
                    <div class="alert alert-light border mt-3 mb-0 py-2">
                        <small>
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            <strong>Administrateur</strong> : acces complet —
                            <strong>Pharmacien</strong> : stock + ventes + commandes —
                            <strong>Caissier</strong> : ventes uniquement
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5" id="btnSoumettre">
                        <i class="bi bi-check-lg me-2"></i>
                        <span id="btnTexte">Creer l'utilisateur</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODALE CONFIRMATION TOGGLE ACTIF                             -->
<!-- ============================================================ -->
<div class="modal fade" id="modalToggle" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="toggleHeader">
                <h5 class="modal-title" id="toggleTitre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <span id="toggleMessage"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/admin/toggle-actif">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_utilisateur" id="toggleId">
                    <button type="submit" class="btn btn-sm" id="toggleBtn">Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODALE CONFIRMATION REINIT MDP                               -->
<!-- ============================================================ -->
<div class="modal fade" id="modalReinit" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-key me-2"></i>Reinitialiser le mot de passe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Un nouveau mot de passe temporaire sera genere pour
                <strong id="reinitNom"></strong>.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/admin/reinit-mdp">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_utilisateur" id="reinitId">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-key me-1"></i>Generer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    var BASE = <?= json_encode(BASE_URL) ?>;

    // ── Modale creation ──────────────────────────────────────
    document.getElementById('modalUtilisateur').addEventListener('show.bs.modal', function(e) {
        if (e.relatedTarget) resetModalCreation();
    });

    function resetModalCreation() {
        document.getElementById('modalTitre').textContent      = 'Nouvel utilisateur';
        document.getElementById('modalIcon').className         = 'bi bi-person-plus text-primary me-2';
        document.getElementById('btnTexte').textContent        = "Creer l'utilisateur";
        document.getElementById('formUtilisateur').action      = BASE + '/admin/creer-utilisateur';
        document.getElementById('inputIdUtilisateur').value    = '';
        document.getElementById('inputNom').value              = '';
        document.getElementById('inputPrenom').value           = '';
        document.getElementById('inputLogin').value            = '';
        document.getElementById('inputEmail').value            = '';
        document.getElementById('inputRole').value             = '';
        document.getElementById('inputMdp').value              = '';
        document.getElementById('inputConfirm').value          = '';
        document.getElementById('mdpObligatoire').classList.remove('d-none');
        document.getElementById('mdpOptionnel').classList.add('d-none');
        document.getElementById('inputMdp').required = true;
        resetForce();
    }

    // ── Modale edition ───────────────────────────────────────
    window.ouvrirEdition = function(id, nom, prenom, email, role, login) {
        document.getElementById('modalTitre').textContent      = 'Modifier l\'utilisateur';
        document.getElementById('modalIcon').className         = 'bi bi-pencil-square text-primary me-2';
        document.getElementById('btnTexte').textContent        = 'Enregistrer';
        document.getElementById('formUtilisateur').action      = BASE + '/admin/modifier-utilisateur';
        document.getElementById('inputIdUtilisateur').value    = id;
        document.getElementById('inputNom').value              = nom;
        document.getElementById('inputPrenom').value           = prenom;
        document.getElementById('inputLogin').value            = login || '';
        document.getElementById('inputEmail').value            = email;
        document.getElementById('inputRole').value             = role;
        document.getElementById('inputMdp').value              = '';
        document.getElementById('inputConfirm').value          = '';
        document.getElementById('mdpObligatoire').classList.add('d-none');
        document.getElementById('mdpOptionnel').classList.remove('d-none');
        document.getElementById('inputMdp').required = false;
        // En edition, login non modifiable
        document.getElementById('inputLogin').readOnly = true;
        document.getElementById('loginAutoInfo').innerHTML = '<i class="bi bi-lock me-1"></i>Login non modifiable apres creation';
        resetForce();
        new bootstrap.Modal(document.getElementById('modalUtilisateur')).show();
    };

    // Auto-generer login depuis nom+prenom (creation uniquement)
    function genererLogin(nom, prenom) {
        var base = (prenom.charAt(0) + nom).toLowerCase();
        return base.replace(/[^a-z0-9]/g, '');
    }
    document.getElementById('inputNom').addEventListener('input', autoLogin);
    document.getElementById('inputPrenom').addEventListener('input', autoLogin);
    function autoLogin() {
        var loginInput = document.getElementById('inputLogin');
        if (loginInput.readOnly) return; // Ne pas toucher en edition
        var nom    = document.getElementById('inputNom').value.trim();
        var prenom = document.getElementById('inputPrenom').value.trim();
        if (nom) loginInput.value = genererLogin(nom, prenom);
    }

    // ── Force du mot de passe ────────────────────────────────
    document.getElementById('inputMdp').addEventListener('input', function() {
        var mdp    = this.value;
        var barre  = document.getElementById('forceBarre');
        var force  = 0;
        if (mdp.length >= 6)  force += 25;
        if (mdp.length >= 10) force += 25;
        if (/[A-Z]/.test(mdp)) force += 25;
        if (/[0-9!@#$%^&*]/.test(mdp)) force += 25;

        var color = force <= 25 ? 'bg-danger' : force <= 50 ? 'bg-warning' : force <= 75 ? 'bg-info' : 'bg-success';
        barre.className = 'progress-bar ' + color;
        barre.style.width = force + '%';
        verifierConfirmation();
    });

    document.getElementById('inputConfirm').addEventListener('input', verifierConfirmation);

    function verifierConfirmation() {
        var mdp     = document.getElementById('inputMdp').value;
        var confirm = document.getElementById('inputConfirm').value;
        var msg     = document.getElementById('msgConfirm');
        if (!confirm) { msg.textContent = ''; return; }
        if (mdp === confirm) {
            msg.innerHTML = '<span class="text-success"><i class="bi bi-check"></i> Identique</span>';
        } else {
            msg.innerHTML = '<span class="text-danger"><i class="bi bi-x"></i> Differents</span>';
        }
    }

    function resetForce() {
        var barre = document.getElementById('forceBarre');
        barre.style.width = '0%';
        barre.className   = 'progress-bar';
        document.getElementById('msgConfirm').textContent = '';
    }

    // ── Visibilite mot de passe ──────────────────────────────
    window.toggleMdpVisibilite = function() {
        var input = document.getElementById('inputMdp');
        var icon  = document.getElementById('iconOeil');
        if (input.type === 'password') {
            input.type    = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type    = 'password';
            icon.className = 'bi bi-eye';
        }
    };

    // ── Confirmation toggle actif ────────────────────────────
    window.confirmerToggle = function(id, nom, estActif) {
        document.getElementById('toggleId').value  = id;
        var header  = document.getElementById('toggleHeader');
        var titre   = document.getElementById('toggleTitre');
        var message = document.getElementById('toggleMessage');
        var btn     = document.getElementById('toggleBtn');

        if (estActif) {
            header.className       = 'modal-header bg-danger text-white';
            titre.textContent      = 'Desactiver l\'utilisateur';
            message.innerHTML      = 'Desactiver <strong>' + nom + '</strong> ? Il ne pourra plus se connecter.';
            btn.className          = 'btn btn-danger btn-sm';
            btn.textContent        = 'Desactiver';
        } else {
            header.className       = 'modal-header bg-success text-white';
            titre.textContent      = 'Reactiver l\'utilisateur';
            message.innerHTML      = 'Reactiver <strong>' + nom + '</strong> ?';
            btn.className          = 'btn btn-success btn-sm';
            btn.textContent        = 'Reactiver';
        }
        new bootstrap.Modal(document.getElementById('modalToggle')).show();
    };

    // ── Confirmation reinit mdp ──────────────────────────────
    window.confirmerReinitMdp = function(id, nom) {
        document.getElementById('reinitId').value       = id;
        document.getElementById('reinitNom').textContent = nom;
        new bootstrap.Modal(document.getElementById('modalReinit')).show();
    };

    // ── Rouvrir modale si erreur validation ──────────────────
    <?php if ($ouvrirModal): ?>
    (function() {
        var form = document.getElementById('formUtilisateur');
        <?php if (!empty($formOld['id_utilisateur'])): ?>
        form.action = BASE + '/admin/modifier-utilisateur';
        document.getElementById('inputIdUtilisateur').value = '<?= (int)$formOld['id_utilisateur'] ?>';
        document.getElementById('modalTitre').textContent = 'Modifier l\'utilisateur';
        document.getElementById('btnTexte').textContent   = 'Enregistrer';
        document.getElementById('mdpObligatoire').classList.add('d-none');
        document.getElementById('mdpOptionnel').classList.remove('d-none');
        document.getElementById('inputMdp').required = false;
        <?php else: ?>
        form.action = BASE + '/admin/creer-utilisateur';
        <?php endif; ?>
        new bootstrap.Modal(document.getElementById('modalUtilisateur')).show();
    })();
    <?php endif; ?>

}); // fin DOMContentLoaded
</script>