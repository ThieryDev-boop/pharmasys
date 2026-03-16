<?php // app/views/clients/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people text-primary me-2"></i>Clients
        <span class="badge bg-secondary ms-2 fs-6"><?= $total ?></span>
    </h4>
    <!-- Bouton ouvre la modale de creation -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalClient">
        <i class="bi bi-person-plus me-1"></i>Nouveau client
    </button>
</div>

<!-- Messages succes -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?php
    $msgs = [
        'cree'    => 'Client cree avec succes.',
        'modifie' => 'Client modifie avec succes.',
    ];
    echo $msgs[$_GET['success']] ?? 'Operation reussie.';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/clients" class="d-flex gap-2">
            <input type="text" name="search" class="form-control"
                placeholder="Rechercher par nom, prenom ou telephone..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/clients" class="btn btn-outline-secondary">
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
                        <th class="ps-3" style="background-color:#8B0000; color: white;">Nom complet</th>
                        <th style="background-color:#8B0000; color: white;">Telephone</th>
                        <th style="background-color:#8B0000; color: white;">Adresse</th>
                        <th class="text-center" style="background-color:#8B0000; color: white;">Nb achats</th>
                        <th class="text-end" style="background-color:#8B0000; color: white;">Total depense</th>
                        <th class="text-center" style="background-color:#8B0000; color: white;">Dernier achat</th>
                        <th class="text-center pe-3" style="background-color:#8B0000; color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            <?= $search ? 'Aucun resultat.' : 'Aucun client enregistre.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary
                                            d-flex align-items-center justify-content-center fw-bold"
                                     style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0;">
                                    <?= strtoupper(substr($c['nom'], 0, 1)) ?>
                                    <?= strtoupper(substr($c['prenom'] ?? '', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($c['nom'] . ' ' . ($c['prenom'] ?? '')) ?>
                                    </div>
                                    <small class="text-muted">
                                        Depuis <?= date('d/m/Y', strtotime($c['date_creation'])) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($c['telephone']): ?>
                            <a href="tel:<?= htmlspecialchars($c['telephone']) ?>"
                               class="text-decoration-none small">
                                <i class="bi bi-telephone me-1 text-muted"></i>
                                <?= htmlspecialchars($c['telephone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $c['adresse'] ? htmlspecialchars($c['adresse']) : '—' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3">
                                <?= $c['nb_achats'] ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= $c['total_achats'] > 0
                                ? number_format($c['total_achats'], 0, ',', ' ') . ' F'
                                : '<span class="text-muted">0 F</span>' ?>
                        </td>
                        <td class="text-center small text-muted">
                            <?= $c['dernier_achat']
                                ? date('d/m/Y', strtotime($c['dernier_achat']))
                                : '—' ?>
                        </td>
                        <td class="text-center pe-3">
                            <!-- Historique -->
                            <a href="<?= BASE_URL ?>/clients/historique?id=<?= $c['id_client'] ?>"
                               class="btn btn-sm btn-outline-info me-1" title="Historique">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <!-- Modifier (ouvre la modale en mode edition) -->
                            <button type="button"
                                class="btn btn-sm btn-outline-primary me-1"
                                title="Modifier"
                                onclick="ouvrirEdition(
                                    <?= $c['id_client'] ?>,
                                    '<?= htmlspecialchars(addslashes($c['nom'])) ?>',
                                    '<?= htmlspecialchars(addslashes($c['prenom'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($c['telephone'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($c['adresse'] ?? '')) ?>'
                                )">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Desactiver -->
                            <?php if (Auth::hasRole('administrateur', 'pharmacien')): ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                title="Desactiver"
                                onclick="confirmerDesactivation(
                                    <?= $c['id_client'] ?>,
                                    '<?= htmlspecialchars(addslashes($c['nom'] . ' ' . ($c['prenom'] ?? ''))) ?>'
                                )">
                                <i class="bi bi-person-x"></i>
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
            <a class="page-link"
               href="<?= BASE_URL ?>/clients?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>


<!-- ============================================================ -->
<!-- MODALE CREATION / EDITION CLIENT                             -->
<!-- ============================================================ -->
<div class="modal fade" id="modalClient" tabindex="-1" aria-labelledby="modalClientLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalClientLabel">
                    <i class="bi bi-person-plus text-primary me-2" id="modalIcon"></i>
                    <span id="modalTitre">Nouveau client</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="formClient">
                <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                <!-- id_client rempli en mode edition -->
                <input type="hidden" name="id_client" id="inputIdClient" value="">

                <div class="modal-body pt-3">

                    <!-- Erreurs eventuelles -->
                    <div id="modalErrors" class="alert alert-danger d-none py-2 mb-3"></div>

                    <!-- Nom -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom" id="inputNom"
                            class="form-control"
                            placeholder="Nom de famille"
                            required>
                    </div>

                    <!-- Prenom -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prenom</label>
                        <input type="text" name="prenom" id="inputPrenom"
                            class="form-control"
                            placeholder="Prenom (optionnel)">
                    </div>

                    <!-- Telephone -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telephone</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-telephone"></i>
                            </span>
                            <input type="text" name="telephone" id="inputTelephone"
                                class="form-control"
                                placeholder="Ex: +237 6XX XXX XXX">
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Adresse</label>
                        <textarea name="adresse" id="inputAdresse"
                            class="form-control" rows="2"
                            placeholder="Quartier, ville (optionnel)"></textarea>
                    </div>

                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-5" id="btnSubmitModal">
                        <i class="bi bi-person-plus me-2" id="btnIcon"></i>
                        <span id="btnTexte">Creer le client</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODALE CONFIRMATION DESACTIVATION                            -->
<!-- ============================================================ -->
<div class="modal fade" id="modalDesactiver" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-person-x me-2"></i>Confirmer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Desactiver <strong id="nomClientDesactiver"></strong> ?
                <br><small class="text-muted">L'historique sera conserve.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/clients/desactiver">
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    <input type="hidden" name="id_client" id="idClientDesactiver">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-person-x me-1"></i>Desactiver
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
const baseUrl = '<?= BASE_URL ?>';

// ── Reinitialiser la modale en mode CREATION ─────────────────
document.getElementById('modalClient').addEventListener('show.bs.modal', function(e) {
    // Si ouvert par le bouton "Nouveau client" (pas par ouvrirEdition)
    if (!e.relatedTarget || e.relatedTarget.tagName === 'BUTTON') {
        resetModalCreation();
    }
});

function resetModalCreation() {
    document.getElementById('modalTitre').textContent   = 'Nouveau client';
    document.getElementById('modalIcon').className      = 'bi bi-person-plus text-primary me-2';
    document.getElementById('btnTexte').textContent     = 'Creer le client';
    document.getElementById('btnIcon').className        = 'bi bi-person-plus me-2';
    document.getElementById('formClient').action        = baseUrl + '/clients/create';
    document.getElementById('inputIdClient').value      = '';
    document.getElementById('inputNom').value           = '';
    document.getElementById('inputPrenom').value        = '';
    document.getElementById('inputTelephone').value     = '';
    document.getElementById('inputAdresse').value       = '';
    document.getElementById('modalErrors').classList.add('d-none');
}

// ── Ouvrir la modale en mode EDITION ─────────────────────────
function ouvrirEdition(id, nom, prenom, telephone, adresse) {
    document.getElementById('modalTitre').textContent   = 'Modifier le client';
    document.getElementById('modalIcon').className      = 'bi bi-person-gear text-primary me-2';
    document.getElementById('btnTexte').textContent     = 'Enregistrer';
    document.getElementById('btnIcon').className        = 'bi bi-check-lg me-2';
    document.getElementById('formClient').action        = baseUrl + '/clients/edit';
    document.getElementById('inputIdClient').value      = id;
    document.getElementById('inputNom').value           = nom;
    document.getElementById('inputPrenom').value        = prenom;
    document.getElementById('inputTelephone').value     = telephone;
    document.getElementById('inputAdresse').value       = adresse;
    document.getElementById('modalErrors').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('modalClient')).show();
}

// ── Confirmation desactivation ────────────────────────────────
function confirmerDesactivation(id, nom) {
    document.getElementById('idClientDesactiver').value      = id;
    document.getElementById('nomClientDesactiver').textContent = nom;
    new bootstrap.Modal(document.getElementById('modalDesactiver')).show();
}
</script>