<?php
// app/views/medicaments/form.php
// Sert pour la creation ET la modification
$estEdition = isset($med) && !empty($med);
$titre      = $estEdition ? 'Modifier le medicament' : 'Nouveau medicament';

// Valeurs du formulaire (depuis $old en cas d'erreur, ou $med en edition)
$v = array_merge([
    'nom_commercial'     => '',
    'dci'                => '',
    'forme_galenique'    => '',
    'dosage'             => '',
    'id_categorie'       => '',
    'conditionnement'    => 1,
    'code_barres'        => '',
    'prix_achat'         => '',
    'prix_vente'         => '',
    'tva'                => 0,
    'seuil_minimum'      => 5,
    'ordonnance_requise' => 0,
], $old ?? []);

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
        <i class="bi bi-<?= $estEdition ? 'pencil-square' : 'plus-circle' ?> text-primary me-2"></i>
        <?= $titre ?>
    </h4>
    <a href="<?= BASE_URL ?>/medicaments" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<form method="POST" action="<?= BASE_URL ?>/medicaments/<?= $estEdition ? 'edit' : 'create' ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php if ($estEdition): ?>
    <input type="hidden" name="id_medicament" value="<?= $med['id_medicament'] ?>">
    <?php endif; ?>

    <div class="row g-4">

        <!-- Colonne gauche : Informations principales -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold py-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>Informations principales
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nom commercial <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom_commercial"
                                class="form-control <?= isInvalid($errors, 'nom_commercial') ?>"
                                value="<?= htmlspecialchars($v['nom_commercial']) ?>"
                                placeholder="Ex: Paracetamol Biogaran" required>
                            <?= err($errors, 'nom_commercial') ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">DCI</label>
                            <input type="text" name="dci" class="form-control"
                                value="<?= htmlspecialchars($v['dci']) ?>"
                                placeholder="Ex: Paracetamol">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Forme galenique</label>
                            <select name="forme_galenique" class="form-select">
                                <option value="">-- Choisir --</option>
                                <?php
                                $formes = ['Comprime','Gelule','Sirop','Injectable','Creme','Pommade',
                                           'Collyre','Suppositoire','Patch','Solution buvable','Poudre'];
                                foreach ($formes as $f):
                                ?>
                                <option value="<?= $f ?>" <?= ($v['forme_galenique'] == $f) ? 'selected' : '' ?>>
                                    <?= $f ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Dosage</label>
                            <input type="text" name="dosage" class="form-control"
                                value="<?= htmlspecialchars($v['dosage']) ?>"
                                placeholder="Ex: 500mg, 250mg/5ml">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Conditionnement</label>
                            <div class="input-group">
                                <input type="number" name="conditionnement" class="form-control"
                                    value="<?= (int)$v['conditionnement'] ?>"
                                    min="1" placeholder="Ex: 30">
                                <span class="input-group-text">unites</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categorie</label>
                            <select name="id_categorie" class="form-select">
                                <option value="">-- Sans categorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_categorie'] ?>"
                                    <?= ($v['id_categorie'] == $cat['id_categorie']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Code-barres (EAN)</label>
                            <input type="text" name="code_barres" class="form-control"
                                value="<?= htmlspecialchars($v['code_barres']) ?>"
                                placeholder="Optionnel">
                        </div>

                    </div>
                </div>
            </div>

            <!-- Prix -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">
                    <i class="bi bi-cash text-success me-2"></i>Prix
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prix d'achat HT</label>
                            <div class="input-group">
                                <input type="number" name="prix_achat" class="form-control"
                                    value="<?= $v['prix_achat'] ?>"
                                    step="1" min="0" placeholder="0">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Prix de vente TTC <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="prix_vente"
                                    class="form-control <?= isInvalid($errors, 'prix_vente') ?>"
                                    value="<?= $v['prix_vente'] ?>"
                                    step="1" min="1" placeholder="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <?= err($errors, 'prix_vente') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">TVA (%)</label>
                            <div class="input-group">
                                <input type="number" name="tva" class="form-control"
                                    value="<?= $v['tva'] ?>"
                                    step="0.5" min="0" max="100" placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite : Parametres stock -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold py-3">
                    <i class="bi bi-boxes text-warning me-2"></i>Parametres stock
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Seuil minimum</label>
                        <input type="number" name="seuil_minimum" class="form-control"
                            value="<?= (int)$v['seuil_minimum'] ?>"
                            min="0" placeholder="5">
                        <small class="text-muted">Alerte declenchee en dessous de ce seuil.</small>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input type="checkbox" name="ordonnance_requise"
                            class="form-check-input" id="ordonnanceCheck"
                            <?= $v['ordonnance_requise'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="ordonnanceCheck">
                            Ordonnance obligatoire
                        </label>
                        <div class="small text-muted mt-1">
                            La vente sera bloquee sans ordonnance.
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($estEdition && !empty($lots)): ?>
            <!-- Lots existants -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">
                    <i class="bi bi-box-seam text-info me-2"></i>Lots en stock
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($lots as $lot): ?>
                        <?php
                        $jours = (strtotime($lot['date_peremption']) - time()) / 86400;
                        $couleur = $jours < 0 ? 'danger' : ($jours < 90 ? 'warning' : 'success');
                        ?>
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">Lot <?= htmlspecialchars($lot['numero_lot']) ?></span>
                                <span class="badge bg-<?= $couleur ?>">
                                    <?= $lot['quantite_restante'] ?> unites
                                </span>
                            </div>
                            <small class="text-muted">
                                Exp: <?= date('d/m/Y', strtotime($lot['date_peremption'])) ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- .row -->

    <!-- Boutons -->
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-5">
            <i class="bi bi-<?= $estEdition ? 'check-lg' : 'plus-circle' ?> me-2"></i>
            <?= $estEdition ? 'Enregistrer les modifications' : 'Creer le medicament' ?>
        </button>
        <a href="<?= BASE_URL ?>/medicaments" class="btn btn-outline-secondary px-4">
            Annuler
        </a>
    </div>

</form>
