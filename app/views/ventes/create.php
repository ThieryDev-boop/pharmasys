<?php // app/views/ventes/create.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-cart-plus text-primary me-2"></i>Nouvelle vente
    </h4>
    <a href="<?= BASE_URL ?>/ventes" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Historique
    </a>
</div>

<?php if (!empty($erreur)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Colonne gauche : saisie -->
    <div class="col-lg-7">

        <!-- Etape 1 : Client -->
        <div class="card border-0 shadow-sm mb-3" id="cardClient">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-person text-primary me-2"></i>Client
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <select class="form-select" id="selectClient">
                            <option value="">-- Client de passage --</option>
                            <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id_client'] ?>">
                                <?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?>
                                <?= $c['telephone'] ? '— ' . $c['telephone'] : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100" id="btnInitVente" onclick="initVente()">
                            <i class="bi bi-play-fill me-1"></i>Demarrer la vente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Etape 2 : Ajouter medicaments (masque au debut) -->
        <div class="card border-0 shadow-sm mb-3 d-none" id="cardMedicaments">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-capsule text-success me-2"></i>Ajouter un medicament
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Medicament</label>
                        <input type="text" class="form-control" id="searchMed"
                            placeholder="Nom, DCI..." autocomplete="off">
                        <div id="suggestionsBox" class="list-group position-absolute w-auto shadow-sm z-3"
                             style="max-width:350px; display:none;"></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Quantite</label>
                        <input type="number" class="form-control" id="inputQte" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Remise %</label>
                        <input type="number" class="form-control" id="inputRemise" value="0" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success w-100" onclick="ajouterLigne()">
                            <i class="bi bi-plus-circle me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
                <div id="msgErreurLigne" class="alert alert-danger mt-2 d-none py-2"></div>
            </div>
        </div>

        <!-- Tableau des lignes -->
        <div class="card border-0 shadow-sm d-none" id="cardLignes">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-list-ul text-info me-2"></i>Lignes de vente
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0" id="tableLignes">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Medicament</th>
                            <th class="text-center">Lot</th>
                            <th class="text-center">Qte</th>
                            <th class="text-end">P.U.</th>
                            <th class="text-end">Montant</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyLignes">
                        <tr id="trVide">
                            <td colspan="6" class="text-center text-muted py-3">
                                Aucun medicament ajoute.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Colonne droite : Total et validation -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;" id="cardTotal">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-calculator text-warning me-2"></i>Recapitulatif
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total TTC</span>
                    <span class="fw-bold fs-4 text-primary" id="affTotal">0 F</span>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Montant recu (F CFA)</label>
                    <input type="number" class="form-control form-control-lg"
                        id="montantPaye" placeholder="0" min="0"
                        oninput="calculerMonnaie()">
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Monnaie a rendre</span>
                    <span class="fw-bold fs-5 text-success" id="affMonnaie">0 F</span>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/ventes/valider" id="formValider">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_vente"    id="hiddenIdVente">
                    <input type="hidden" name="montant_paye" id="hiddenMontantPaye">

                    <button type="button" class="btn btn-success w-100 btn-lg d-none"
                        id="btnValider" onclick="validerVente()">
                        <i class="bi bi-check-circle me-2"></i>Valider la vente
                    </button>

                    <div class="text-center text-muted mt-3 d-none" id="indications">
                        Demarrez la vente et ajoutez des medicaments.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ordonnance -->
<div class="modal fade" id="modalOrdonnance" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-file-medical me-2"></i>Ordonnance requise
                </h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">Ce medicament necessite une ordonnance valide.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Medecin prescripteur</label>
                    <input type="text" class="form-control" id="ordMedecin" placeholder="Dr. Nom Prenom">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom du patient</label>
                    <input type="text" class="form-control" id="ordPatient" placeholder="Nom complet">
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">N° Ordonnance</label>
                        <input type="text" class="form-control" id="ordNumero" placeholder="Optionnel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date ordonnance</label>
                        <input type="date" class="form-control" id="ordDate"
                            value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    onclick="annulerOrdonnance()">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmerOrdonnance()">
                    <i class="bi bi-check me-1"></i>Confirmer et ajouter
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let idVente        = <?= isset($vente) ? $vente['id_vente'] : 'null' ?>;
let venteInitiee   = <?= isset($vente) ? 'true' : 'false' ?>;
let totalRaw       = 0;
let medEnAttente   = null; // medicament en attente de confirmation ordonnance
const csrfToken    = '<?= htmlspecialchars($csrfToken) ?>';
const baseUrl      = '<?= BASE_URL ?>';

// ── Initialiser la vente ─────────────────────────────────────
function initVente() {
    if (venteInitiee) return;

    const idClient = document.getElementById('selectClient').value;
    fetch(baseUrl + '/ventes/init', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=' + encodeURIComponent(csrfToken)
            + '&id_client=' + encodeURIComponent(idClient)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            idVente      = data.id_vente;
            venteInitiee = true;
            document.getElementById('hiddenIdVente').value = idVente;
            document.getElementById('btnInitVente').disabled = true;
            document.getElementById('btnInitVente').textContent = 'Vente en cours #' + idVente;
            document.getElementById('selectClient').disabled = true;
            document.getElementById('cardMedicaments').classList.remove('d-none');
            document.getElementById('cardLignes').classList.remove('d-none');
            document.getElementById('btnValider').classList.remove('d-none');
        }
    });
}

// ── Recherche medicament ─────────────────────────────────────
let rechercheTimer = null;
document.getElementById('searchMed').addEventListener('input', function() {
    clearTimeout(rechercheTimer);
    const terme = this.value.trim();
    if (terme.length < 2) {
        document.getElementById('suggestionsBox').style.display = 'none'; return;
    }
    rechercheTimer = setTimeout(() => {
        fetch(baseUrl + '/ventes/rechercher-medicament?q=' + encodeURIComponent(terme))
        .then(r => r.json())
        .then(afficherSuggestions);
    }, 300);
});

function afficherSuggestions(resultats) {
    const box = document.getElementById('suggestionsBox');
    if (!resultats.length) { box.style.display = 'none'; return; }

    box.innerHTML = '';
    resultats.forEach(med => {
        const stock = parseInt(med.stock_total);
        const stockBadge = stock > 0
            ? '<span class="badge bg-success ms-2">' + stock + '</span>'
            : '<span class="badge bg-danger ms-2">Rupture</span>';
        const ordBadge = med.ordonnance_requise
            ? '<span class="badge bg-warning text-dark ms-1">Ord.</span>' : '';

        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action py-2 px-3';
        item.innerHTML = '<div class="fw-semibold">' + med.nom_commercial + ordBadge + stockBadge + '</div>'
            + '<small class="text-muted">' + (med.dci || '') + ' — ' + (med.forme_galenique || '') + ' ' + (med.dosage || '') + '</small>';
        item.onclick = () => selectionnerMedicament(med);
        box.appendChild(item);
    });
    box.style.display = 'block';
}

let medSelectionne = null;
function selectionnerMedicament(med) {
    medSelectionne = med;
    document.getElementById('searchMed').value = med.nom_commercial;
    document.getElementById('suggestionsBox').style.display = 'none';
}

// Fermer suggestions en cliquant ailleurs
document.addEventListener('click', e => {
    if (!e.target.closest('#searchMed') && !e.target.closest('#suggestionsBox')) {
        document.getElementById('suggestionsBox').style.display = 'none';
    }
});

// ── Ajouter une ligne ────────────────────────────────────────
function ajouterLigne(ordonnanceOk = false) {
    if (!venteInitiee) { alert('Demarrez la vente dabord.'); return; }
    if (!medSelectionne) { alert('Selectionnez un medicament.'); return; }

    const qte    = parseInt(document.getElementById('inputQte').value);
    const remise = parseFloat(document.getElementById('inputRemise').value) || 0;

    if (qte <= 0) { alert('Quantite invalide.'); return; }

    const body = 'csrf_token='     + encodeURIComponent(csrfToken)
        + '&id_vente='             + idVente
        + '&id_medicament='        + medSelectionne.id_medicament
        + '&quantite='             + qte
        + '&remise='               + remise
        + (ordonnanceOk ? '&ordonnance_ok=1' : '');

    fetch(baseUrl + '/ventes/ajouter-ligne', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            rafraichirLignes(data.lignes, data.total_ttc, data.total_raw);
            document.getElementById('searchMed').value = '';
            document.getElementById('inputQte').value  = 1;
            document.getElementById('inputRemise').value = 0;
            medSelectionne = null;
            document.getElementById('msgErreurLigne').classList.add('d-none');
        } else if (data.requires_ordonnance) {
            medEnAttente = { qte, remise };
            new bootstrap.Modal(document.getElementById('modalOrdonnance')).show();
        } else {
            const msg = document.getElementById('msgErreurLigne');
            msg.textContent = data.message;
            msg.classList.remove('d-none');
        }
    });
}

function confirmerOrdonnance() {
    bootstrap.Modal.getInstance(document.getElementById('modalOrdonnance')).hide();
    ajouterLigne(true);
}
function annulerOrdonnance() { medEnAttente = null; }

// ── Supprimer une ligne ──────────────────────────────────────
function supprimerLigne(idLigne) {
    if (!confirm('Supprimer cette ligne ?')) return;
    fetch(baseUrl + '/ventes/supprimer-ligne', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=' + encodeURIComponent(csrfToken)
            + '&id_ligne='  + idLigne
            + '&id_vente='  + idVente
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            rafraichirLignes(data.lignes, data.total_ttc, data.total_raw);
        }
    });
}

// ── Rafraichir tableau des lignes ────────────────────────────
function rafraichirLignes(lignes, totalFormate, total) {
    totalRaw = total;
    document.getElementById('affTotal').textContent = totalFormate + ' F';
    document.getElementById('hiddenIdVente').value = idVente;

    const tbody = document.getElementById('tbodyLignes');
    tbody.innerHTML = '';

    if (!lignes || lignes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Aucun medicament ajoute.</td></tr>';
        return;
    }

    lignes.forEach(l => {
        tbody.innerHTML += `<tr>
            <td class="ps-3">
                <div class="fw-semibold">${l.nom_commercial}</div>
                <small class="text-muted">${l.dci || ''}</small>
            </td>
            <td class="text-center"><small class="badge bg-light text-dark border">${l.numero_lot}</small></td>
            <td class="text-center">${l.quantite}</td>
            <td class="text-end">${parseInt(l.prix_unitaire).toLocaleString('fr')} F</td>
            <td class="text-end fw-semibold">${parseInt(l.montant_ligne).toLocaleString('fr')} F</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(${l.id_ligne})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    });

    calculerMonnaie();
}

// ── Calculer monnaie ─────────────────────────────────────────
function calculerMonnaie() {
    const paye    = parseFloat(document.getElementById('montantPaye').value) || 0;
    const monnaie = paye - totalRaw;
    const span    = document.getElementById('affMonnaie');
    span.textContent = Math.max(0, monnaie).toLocaleString('fr') + ' F';
    span.className   = monnaie < 0 ? 'fw-bold fs-5 text-danger' : 'fw-bold fs-5 text-success';
}

// ── Valider la vente ─────────────────────────────────────────
function validerVente() {
    if (totalRaw <= 0) { alert('Ajoutez au moins un medicament.'); return; }
    const paye = parseFloat(document.getElementById('montantPaye').value) || 0;
    if (paye < totalRaw) { alert('Montant recu insuffisant.'); return; }

    document.getElementById('hiddenMontantPaye').value = paye;
    document.getElementById('formValider').submit();
}

// Charger les lignes existantes si vente en cours
<?php if (isset($vente) && isset($lignes)): ?>
venteInitiee = true;
idVente = <?= $vente['id_vente'] ?>;
document.getElementById('hiddenIdVente').value = idVente;
const lignesExistantes = <?= json_encode($lignes) ?>;
rafraichirLignes(lignesExistantes, '<?= number_format($vente['montant_total_ttc'], 0, ',', ' ') ?>', <?= (float)$vente['montant_total_ttc'] ?>);
<?php endif; ?>
</script>
