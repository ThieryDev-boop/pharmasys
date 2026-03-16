<?php // app/views/fournisseurs/index.php ?>

<?php
// Recuperer erreurs depuis session si redirection apres erreur
$errors   = $_SESSION['form_errors'] ?? [];
$formOld  = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
$ouvrirModal = !empty($_GET['erreur']) || !empty($formOld);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-truck text-primary me-2"></i>Fournisseurs
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <button type="button" class="btn btn-primary"
        data-bs-toggle="modal" data-bs-target="#modalFournisseur">
        <i class="bi bi-plus-circle me-1"></i>Nouveau fournisseur
    </button>
</div>

<!-- Messages -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= $_GET['success'] === 'cree' ? 'Fournisseur cree avec succes.' : 'Fournisseur modifie avec succes.' ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/fournisseurs" class="d-flex gap-2">
            <input type="text" name="search" class="form-control"
                placeholder="Rechercher par raison sociale, contact ou telephone..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/fournisseurs" class="btn btn-outline-secondary">
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
                        <th class="ps-3" style="background-color:#8B0000; color: white;">Raison sociale</th>
                        <th style="background-color:#8B0000; color: white;">Contact</th>
                        <th style="background-color:#8B0000; color: white;">Telephone</th>
                        <th style="background-color:#8B0000; color: white;">Email</th>
                        <th class="text-center" style="background-color:#8B0000; color: white;">Commandes</th>
                        <th class="text-end" style="background-color:#8B0000; color: white;">Total achats</th>
                        <th class="text-center pe-3" style="background-color:#8B0000; color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($fournisseurs)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-truck fs-2 d-block mb-2"></i>
                            <?= $search ? 'Aucun resultat.' : 'Aucun fournisseur enregistre.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($fournisseurs as $f): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-warning bg-opacity-15 text-warning
                                            d-flex align-items-center justify-content-center fw-bold"
                                     style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0;">
                                    <?= strtoupper(substr($f['raison_sociale'], 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($f['raison_sociale']) ?>
                                    </div>
                                    <small class="text-muted">
                                        Depuis <?= date('d/m/Y', strtotime($f['date_creation'])) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td class="small">
                            <?= $f['contact'] ? htmlspecialchars($f['contact']) : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td>
                            <?php if ($f['telephone']): ?>
                            <a href="tel:<?= htmlspecialchars($f['telephone']) ?>"
                               class="text-decoration-none small">
                                <i class="bi bi-telephone me-1 text-muted"></i>
                                <?= htmlspecialchars($f['telephone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($f['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($f['email']) ?>"
                               class="text-decoration-none small">
                                <i class="bi bi-envelope me-1 text-muted"></i>
                                <?= htmlspecialchars($f['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3">
                                <?= $f['nb_commandes'] ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= $f['total_commandes'] > 0
                                ? number_format($f['total_commandes'], 0, ',', ' ') . ' F'
                                : '<span class="text-muted">0 F</span>' ?>
                        </td>
                        <td class="text-center pe-3">
                            <!-- Detail -->
                            <a href="<?= BASE_URL ?>/fournisseurs/detail?id=<?= $f['id_fournisseur'] ?>"
                               class="btn btn-sm btn-outline-info me-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <!-- Modifier -->
                            <button type="button"
                                class="btn btn-sm btn-outline-primary me-1"
                                title="Modifier"
                                onclick="ouvrirEdition(
                                    <?= $f['id_fournisseur'] ?>,
                                    '<?= htmlspecialchars(addslashes($f['raison_sociale'])) ?>',
                                    '<?= htmlspecialchars(addslashes($f['contact'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($f['telephone'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($f['email'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($f['adresse'] ?? '')) ?>'
                                )">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Desactiver -->
                            <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                title="Desactiver"
                                onclick="confirmerDesactivation(
                                    <?= $f['id_fournisseur'] ?>,
                                    '<?= htmlspecialchars(addslashes($f['raison_sociale'])) ?>'
                                )">
                                <i class="bi bi-trash"></i>
                            </button>
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
               href="<?= BASE_URL ?>/fournisseurs?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>


<!-- ====================================================== -->
<!-- MODALE CREATION / EDITION FOURNISSEUR                  -->
<!-- ====================================================== -->
<div class="modal fade" id="modalFournisseur" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitre">
                    <i class="bi bi-plus-circle text-primary me-2" id="modalIcon"></i>
                    <span id="modalTitreTexte">Nouveau fournisseur</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="formFournisseur">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_fournisseur" id="inputIdFournisseur" value="">

                <div class="modal-body pt-3">

                    <!-- Erreurs -->
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">

                        <!-- Raison sociale -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Raison sociale <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="raison_sociale" id="inputRaisonSociale"
                                class="form-control <?= isset($errors['raison_sociale']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($formOld['raison_sociale'] ?? '') ?>"
                                placeholder="Ex: Pharma Distribution SARL"
                                required>
                            <?php if (isset($errors['raison_sociale'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['raison_sociale']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Contact -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom du contact</label>
                            <input type="text" name="contact" id="inputContact"
                                class="form-control"
                                value="<?= htmlspecialchars($formOld['contact'] ?? '') ?>"
                                placeholder="Ex: Jean Dupont">
                        </div>

                        <!-- Telephone -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telephone</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="text" name="telephone" id="inputTelephone"
                                    class="form-control"
                                    value="<?= htmlspecialchars($formOld['telephone'] ?? '') ?>"
                                    placeholder="+237 6XX XXX XXX">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" id="inputEmail"
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formOld['email'] ?? '') ?>"
                                    placeholder="commandes@fournisseur.com">
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Adresse</label>
                            <input type="text" name="adresse" id="inputAdresse"
                                class="form-control"
                                value="<?= htmlspecialchars($formOld['adresse'] ?? '') ?>"
                                placeholder="Ville, quartier">
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-lg me-2" id="btnIcon"></i>
                        <span id="btnTexte">Creer le fournisseur</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ====================================================== -->
<!-- MODALE CONFIRMATION DESACTIVATION                      -->
<!-- ====================================================== -->
<div class="modal fade" id="modalDesactiver" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-trash me-2"></i>Confirmer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Desactiver <strong id="nomFournisseur"></strong> ?
                <br><small class="text-muted">L'historique des commandes sera conserve.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/fournisseurs/desactiver">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_fournisseur" id="idFournisseurDesactiver">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Desactiver
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
const baseUrl = '<?= BASE_URL ?>';

// Reinitialiser modale en mode creation
document.getElementById('modalFournisseur').addEventListener('show.bs.modal', function(e) {
    if (e.relatedTarget) resetModalCreation();
});

function resetModalCreation() {
    document.getElementById('modalTitreTexte').textContent  = 'Nouveau fournisseur';
    document.getElementById('modalIcon').className          = 'bi bi-plus-circle text-primary me-2';
    document.getElementById('btnTexte').textContent         = 'Creer le fournisseur';
    document.getElementById('formFournisseur').action       = baseUrl + '/fournisseurs/create';
    document.getElementById('inputIdFournisseur').value     = '';
    document.getElementById('inputRaisonSociale').value     = '';
    document.getElementById('inputContact').value           = '';
    document.getElementById('inputTelephone').value         = '';
    document.getElementById('inputEmail').value             = '';
    document.getElementById('inputAdresse').value           = '';
}

// Ouvrir modale en mode edition
function ouvrirEdition(id, rs, contact, tel, email, adresse) {
    document.getElementById('modalTitreTexte').textContent  = 'Modifier le fournisseur';
    document.getElementById('modalIcon').className          = 'bi bi-pencil-square text-primary me-2';
    document.getElementById('btnTexte').textContent         = 'Enregistrer';
    document.getElementById('formFournisseur').action       = baseUrl + '/fournisseurs/edit';
    document.getElementById('inputIdFournisseur').value     = id;
    document.getElementById('inputRaisonSociale').value     = rs;
    document.getElementById('inputContact').value           = contact;
    document.getElementById('inputTelephone').value         = tel;
    document.getElementById('inputEmail').value             = email;
    document.getElementById('inputAdresse').value           = adresse;
    new bootstrap.Modal(document.getElementById('modalFournisseur')).show();
}

// Confirmation desactivation
function confirmerDesactivation(id, nom) {
    document.getElementById('idFournisseurDesactiver').value = id;
    document.getElementById('nomFournisseur').textContent    = nom;
    new bootstrap.Modal(document.getElementById('modalDesactiver')).show();
}

// Rouvrir modale si erreur de validation
<?php if ($ouvrirModal): ?>
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('formFournisseur').action =
        '<?= !empty($formOld['id_fournisseur']) ? BASE_URL.'/fournisseurs/edit' : BASE_URL.'/fournisseurs/create' ?>';
    <?php if (!empty($formOld['id_fournisseur'])): ?>
    document.getElementById('inputIdFournisseur').value = '<?= (int)$formOld['id_fournisseur'] ?>';
    document.getElementById('modalTitreTexte').textContent = 'Modifier le fournisseur';
    document.getElementById('btnTexte').textContent = 'Enregistrer';
    <?php endif; ?>
    new bootstrap.Modal(document.getElementById('modalFournisseur')).show();
});
<?php endif; ?>
</script>
