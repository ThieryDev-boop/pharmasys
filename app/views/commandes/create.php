<?php // app/views/commandes/create.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-clipboard-plus text-primary me-2"></i>Nouvelle commande
    </h4>
    <a href="<?= BASE_URL ?>/commandes" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row g-4">

    <!-- Colonne gauche -->
    <div class="col-lg-7">

        <!-- Étape 1 : Fournisseur -->
        <div class="card border-0 shadow-sm mb-3" id="cardEtape1">
            <div class="card-header bg-white fw-semibold py-3">
                <span class="badge bg-primary me-2">1</span>Fournisseur &amp; informations
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">
                            Fournisseur <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="selectFournisseur">
                            <option value="">-- Sélectionnez --</option>
                            <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= $f['id_fournisseur'] ?>"
                                <?= $idFournisseur == $f['id_fournisseur'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['raison_sociale']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date de livraison prévue</label>
                        <input type="date" class="form-control" id="inputDateLivraison"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Note</label>
                        <input type="text" class="form-control" id="inputNote"
                            placeholder="Note optionnelle...">
                    </div>
                </div>
                <div id="msgInit" class="alert alert-danger mt-3 d-none py-2"></div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary px-4" id="btnDemarrer">
                        <i class="bi bi-play-fill me-2"></i>Démarrer la commande
                    </button>
                </div>
            </div>
        </div>

        <!-- Étape 2 : Médicaments (masqué au départ) -->
        <div class="card border-0 shadow-sm mb-3 d-none" id="cardEtape2">
            <div class="card-header bg-white fw-semibold py-3">
                <span class="badge bg-success me-2">2</span>Ajouter des médicaments
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5 position-relative">
                        <label class="form-label fw-semibold small">Médicament</label>
                        <input type="text" class="form-control" id="searchMed"
                            placeholder="Tapez le nom..." autocomplete="off">
                        <div id="suggestionsBox"
                             class="list-group position-absolute shadow"
                             style="width:100%;display:none;max-height:220px;overflow-y:auto;z-index:1050;top:100%;left:0;">
                        </div>
                        <input type="hidden" id="hiddenIdMedicament">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Quantité</label>
                        <input type="number" class="form-control" id="inputQte"
                            value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Prix achat (F)</label>
                        <input type="number" class="form-control" id="inputPrix"
                            value="0" min="0" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" id="btnAjouter"
                            title="Ajouter">
                            <i class="bi bi-plus-circle-fill"></i>
                        </button>
                    </div>
                </div>
                <div id="msgLigne" class="alert alert-danger mt-2 d-none py-2"></div>
            </div>
        </div>

        <!-- Tableau des lignes -->
        <div class="card border-0 shadow-sm d-none" id="cardLignes">
            <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
                <span><i class="bi bi-list-ul me-2 text-info"></i>Articles</span>
                <span class="badge bg-secondary" id="nbLignes">0</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Médicament</th>
                            <th class="text-center">Qté</th>
                            <th class="text-end">Prix U.</th>
                            <th class="text-end">Montant</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyLignes">
                        <tr id="trVide">
                            <td colspan="5" class="text-center text-muted py-3">
                                Aucun article ajouté.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /col-lg-7 -->

    <!-- Récapitulatif -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-receipt text-warning me-2"></i>Récapitulatif
            </div>
            <div class="card-body">

                <!-- Info commande initialisée -->
                <div id="infoCommande" class="d-none mb-3 p-3 rounded bg-light">
                    <div class="small text-muted">Commande initialisée</div>
                    <div class="fw-bold text-primary fs-5" id="affNumero"></div>
                    <div class="small text-muted" id="affFournisseur"></div>
                </div>

                <!-- Total -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-muted">Total HT</span>
                    <span class="fw-bold fs-3 text-primary" id="affTotal">0 F</span>
                </div>

                <!-- Actions (masquées au départ) -->
                <div id="actionsCommande" class="d-none">
                    <button type="button" class="btn btn-warning w-100 mb-2" id="btnEnvoyer">
                        <i class="bi bi-send me-2"></i>Enregistrer et envoyer
                    </button>
                    <a href="#" id="lienDetail" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-eye me-2"></i>Voir le détail
                    </a>
                    <button type="button" class="btn btn-outline-secondary w-100"
                        id="btnSauvegarderBrouillon">
                        <i class="bi bi-floppy me-2"></i>Sauvegarder en brouillon
                    </button>
                </div>

                <p class="text-center text-muted small" id="msgAttente">
                    <i class="bi bi-arrow-up me-1"></i>
                    Sélectionnez un fournisseur et démarrez.
                </p>
            </div>
        </div>
    </div>

</div><!-- /row -->

<!-- Formulaire caché pour envoyer -->
<form method="POST" action="<?= BASE_URL ?>/commandes/envoyer" id="formEnvoyer" class="d-none">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="id_commande" id="hiddenIdCommande">
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var CSRF        = <?= json_encode($csrfToken) ?>;
    var BASE        = <?= json_encode(BASE_URL) ?>;
    var idCommande  = null;
    var medActuel   = null;
    var timerSearch = null;

    // ── DÉMARRER ─────────────────────────────────────────────
    document.getElementById('btnDemarrer').addEventListener('click', function () {
        var idF  = document.getElementById('selectFournisseur').value;
        var note = document.getElementById('inputNote').value;
        var liv  = document.getElementById('inputDateLivraison').value;

        if (!idF) { affMsg('msgInit', 'Veuillez sélectionner un fournisseur.'); return; }

        var btn = this;
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

        fetch(BASE + '/commandes/init', {
            method : 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body   : 'csrf_token='     + encodeURIComponent(CSRF)
                   + '&id_fournisseur='+ encodeURIComponent(idF)
                   + '&note='          + encodeURIComponent(note)
                   + '&date_livraison='+ encodeURIComponent(liv)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                idCommande = data.id_commande;
                document.getElementById('hiddenIdCommande').value = idCommande;
                document.getElementById('affNumero').textContent    = data.numero;
                document.getElementById('affFournisseur').textContent = data.fournisseur;
                document.getElementById('lienDetail').href          = BASE + '/commandes/detail?id=' + idCommande;

                // Afficher les sections
                document.getElementById('infoCommande').classList.remove('d-none');
                document.getElementById('actionsCommande').classList.remove('d-none');
                document.getElementById('msgAttente').classList.add('d-none');
                document.getElementById('cardEtape2').classList.remove('d-none');
                document.getElementById('cardLignes').classList.remove('d-none');

                // Verrouiller étape 1
                document.getElementById('selectFournisseur').disabled = true;
                document.getElementById('inputNote').disabled         = true;
                document.getElementById('inputDateLivraison').disabled= true;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.numero;

                document.getElementById('msgInit').classList.add('d-none');
            } else {
                affMsg('msgInit', data.message || 'Erreur serveur.');
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Démarrer la commande';
            }
        })
        .catch(function (e) {
            affMsg('msgInit', 'Erreur réseau : ' + e.message);
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Démarrer la commande';
        });
    });

    // ── RECHERCHE MÉDICAMENT ──────────────────────────────────
    document.getElementById('searchMed').addEventListener('input', function () {
        clearTimeout(timerSearch);
        var terme = this.value.trim();
        var box   = document.getElementById('suggestionsBox');
        if (terme.length < 2) { box.style.display = 'none'; return; }

        timerSearch = setTimeout(function () {
            fetch(BASE + '/ventes/rechercher-medicament?q=' + encodeURIComponent(terme))
            .then(function (r) { return r.json(); })
            .then(function (meds) {
                box.innerHTML = '';
                if (!meds.length) { box.style.display = 'none'; return; }
                meds.forEach(function (m) {
                    var btn = document.createElement('button');
                    btn.type      = 'button';
                    btn.className = 'list-group-item list-group-item-action py-2 px-3';
                    btn.innerHTML = '<div class="fw-semibold small">' + m.nom_commercial + '</div>'
                        + '<small class="text-muted">'
                        + (m.dci || '') + ' — ' + (m.forme_galenique || '') + ' ' + (m.dosage || '')
                        + '</small>';
                    btn.addEventListener('click', function () {
                        medActuel = m;
                        document.getElementById('searchMed').value      = m.nom_commercial;
                        document.getElementById('hiddenIdMedicament').value = m.id_medicament;
                        if (m.prix_achat > 0) document.getElementById('inputPrix').value = m.prix_achat;
                        box.style.display = 'none';
                        document.getElementById('inputQte').focus();
                    });
                    box.appendChild(btn);
                });
                box.style.display = 'block';
            });
        }, 300);
    });

    // Fermer suggestions au clic extérieur
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#searchMed') && !e.target.closest('#suggestionsBox')) {
            document.getElementById('suggestionsBox').style.display = 'none';
        }
    });

    // ── AJOUTER LIGNE ─────────────────────────────────────────
    document.getElementById('btnAjouter').addEventListener('click', function () {
        if (!idCommande) { affMsg('msgLigne', 'Démarrez d\'abord la commande.'); return; }
        if (!medActuel)  { affMsg('msgLigne', 'Sélectionnez un médicament.'); return; }

        var qte  = parseInt(document.getElementById('inputQte').value);
        var prix = parseFloat(document.getElementById('inputPrix').value) || 0;
        if (qte <= 0) { affMsg('msgLigne', 'Quantité invalide.'); return; }

        fetch(BASE + '/commandes/ajouter-ligne', {
            method : 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body   : 'csrf_token='    + encodeURIComponent(CSRF)
                   + '&id_commande='  + idCommande
                   + '&id_medicament='+ medActuel.id_medicament
                   + '&quantite='     + qte
                   + '&prix_unitaire='+ prix
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                rafraichirLignes(data.lignes, data.total);
                document.getElementById('searchMed').value        = '';
                document.getElementById('hiddenIdMedicament').value = '';
                document.getElementById('inputQte').value         = 1;
                document.getElementById('inputPrix').value        = 0;
                medActuel = null;
                document.getElementById('msgLigne').classList.add('d-none');
                document.getElementById('searchMed').focus();
            } else {
                affMsg('msgLigne', data.message || 'Erreur.');
            }
        })
        .catch(function (e) { affMsg('msgLigne', 'Erreur réseau : ' + e.message); });
    });

    // Ajouter avec Entrée sur le champ quantité
    document.getElementById('inputQte').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') document.getElementById('btnAjouter').click();
    });

    // ── SUPPRIMER LIGNE ───────────────────────────────────────
    window.supprimerLigne = function (idLigne) {
        if (!confirm('Supprimer cet article ?')) return;
        fetch(BASE + '/commandes/supprimer-ligne', {
            method : 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body   : 'csrf_token='  + encodeURIComponent(CSRF)
                   + '&id_ligne='   + idLigne
                   + '&id_commande='+ idCommande
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) rafraichirLignes(data.lignes, data.total);
        });
    };

    // ── RAFRAÎCHIR ────────────────────────────────────────────
    function rafraichirLignes(lignes, total) {
        var tbody = document.getElementById('tbodyLignes');
        var nb    = document.getElementById('nbLignes');
        nb.textContent = lignes.length;

        if (!lignes.length) {
            tbody.innerHTML = '<tr id="trVide"><td colspan="5" class="text-center text-muted py-3">Aucun article ajouté.</td></tr>';
            document.getElementById('affTotal').textContent = '0 F';
            return;
        }

        tbody.innerHTML = '';
        lignes.forEach(function (l) {
            var montant = (l.quantite_commandee * l.prix_unitaire);
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="ps-3">'
                    + '<div class="fw-semibold small">' + l.nom_commercial + '</div>'
                    + '<small class="text-muted">' + (l.dci || '') + '</small>'
                + '</td>'
                + '<td class="text-center fw-bold">' + l.quantite_commandee + '</td>'
                + '<td class="text-end small">' + parseInt(l.prix_unitaire).toLocaleString('fr') + ' F</td>'
                + '<td class="text-end fw-semibold small text-success">'
                    + parseInt(montant).toLocaleString('fr') + ' F'
                + '</td>'
                + '<td class="text-center">'
                    + '<button type="button" class="btn btn-sm btn-outline-danger px-2"'
                    + ' onclick="supprimerLigne(' + l.id_ligne_cmd + ')">'
                    + '<i class="bi bi-trash"></i></button>'
                + '</td>';
            tbody.appendChild(tr);
        });

        document.getElementById('affTotal').textContent =
            parseInt(total).toLocaleString('fr') + ' F';
    }

    // ── ENVOYER ───────────────────────────────────────────────
    document.getElementById('btnEnvoyer').addEventListener('click', function () {
        if (!idCommande) return;
        var nb = parseInt(document.getElementById('nbLignes').textContent);
        if (nb === 0) { alert('Ajoutez au moins un médicament avant d\'envoyer.'); return; }
        if (confirm('Envoyer la commande ' + document.getElementById('affNumero').textContent + ' au fournisseur ?')) {
            document.getElementById('formEnvoyer').submit();
        }
    });

    // ── BROUILLON ─────────────────────────────────────────────
    document.getElementById('btnSauvegarderBrouillon').addEventListener('click', function () {
        if (!idCommande) return;
        window.location.href = BASE + '/commandes/detail?id=' + idCommande;
    });

    // ── HELPERS ───────────────────────────────────────────────
    function affMsg(id, msg) {
        var el = document.getElementById(id);
        el.textContent = msg;
        el.classList.remove('d-none');
    }

}); // fin DOMContentLoaded
</script>
