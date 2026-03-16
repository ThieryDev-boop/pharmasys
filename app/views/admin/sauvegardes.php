<?php // app/views/admin/sauvegardes.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-cloud-arrow-down text-primary me-2"></i>Sauvegardes de la base de données
    </h4>
    <form method="POST" action="<?= BASE_URL ?>/admin/sauvegardes/creer">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <button type="submit" class="btn btn-primary" id="btnSauvegarder"
            onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Génération...'; this.form.submit();">
            <i class="bi bi-cloud-plus me-2"></i>Créer une sauvegarde
        </button>
    </form>
</div>

<!-- Messages -->
<?php if (!empty($success) && $success !== 'supprimee'): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    Sauvegarde créée avec succès :
    <strong class="font-monospace"><?= htmlspecialchars($success) ?></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php elseif ($success === 'supprimee'): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>Sauvegarde supprimée.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Erreur : <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Info -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex gap-3">
        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>Comment fonctionnent les sauvegardes ?</strong><br>
            <small>
                Chaque sauvegarde génère un fichier <code>.sql</code> complet (structure + données) stocké
                dans le dossier <code>sauvegardes/</code> à la racine du projet.
                Les 10 dernières sauvegardes sont conservées automatiquement.
                Pour restaurer : importez le fichier <code>.sql</code> via phpMyAdmin.
            </small>
        </div>
    </div>
</div>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-files me-1"></i>Sauvegardes</div>
            <div class="fw-bold fs-3 text-primary"><?= count($sauvegardes) ?></div>
            <div class="small text-muted">sur 10 max conservées</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-hdd me-1"></i>Taille totale</div>
            <div class="fw-bold fs-4 text-info">
                <?php
                $total = array_sum(array_column($sauvegardes, 'taille'));
                echo $total >= 1048576
                    ? round($total / 1048576, 1) . ' Mo'
                    : round($total / 1024, 1) . ' Ko';
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-clock-history me-1"></i>Dernière sauvegarde</div>
            <div class="fw-semibold text-success" style="font-size:0.95rem;">
                <?= !empty($sauvegardes)
                    ? $sauvegardes[0]['date_fmt']
                    : '<span class="text-muted">Aucune</span>' ?>
            </div>
        </div>
    </div>
</div>

<!-- Liste des sauvegardes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2 text-primary"></i>Fichiers de sauvegarde</span>
        <span class="badge bg-secondary"><?= count($sauvegardes) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($sauvegardes)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-cloud-slash fs-1 d-block mb-3 opacity-25"></i>
            <p class="mb-1">Aucune sauvegarde disponible.</p>
            <small>Cliquez sur "Créer une sauvegarde" pour commencer.</small>
        </div>
        <?php else: ?>
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Fichier</th>
                    <th class="text-center">Taille</th>
                    <th class="text-center">Date de création</th>
                    <th class="text-center pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sauvegardes as $i => $s): ?>
            <tr>
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-zip text-primary fs-5"></i>
                        <div>
                            <div class="font-monospace small fw-semibold">
                                <?= htmlspecialchars($s['nom']) ?>
                            </div>
                            <?php if ($i === 0): ?>
                            <span class="badge bg-success" style="font-size:0.65rem;">
                                <i class="bi bi-star-fill me-1"></i>Plus récente
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border px-3">
                        <?= $s['taille_fmt'] ?>
                    </span>
                </td>
                <td class="text-center small text-muted">
                    <i class="bi bi-calendar3 me-1"></i><?= $s['date_fmt'] ?>
                </td>
                <td class="text-center pe-3">
                    <!-- Télécharger -->
                    <a href="<?= BASE_URL ?>/admin/sauvegardes/telecharger?fichier=<?= urlencode($s['nom']) ?>"
                       class="btn btn-sm btn-outline-primary me-1"
                       title="Télécharger">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                    <!-- Supprimer -->
                    <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        title="Supprimer"
                        onclick="confirmerSuppression('<?= htmlspecialchars(addslashes($s['nom'])) ?>')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modale confirmation suppression -->
<div class="modal fade" id="modalSupprimer" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Supprimer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Supprimer définitivement <strong id="nomFichierSuppr"></strong> ?
                <div class="text-muted small mt-1">Cette action est irréversible.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>/admin/sauvegardes/supprimer">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="fichier" id="inputFichierSuppr">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Guide de restauration -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-arrow-counterclockwise text-warning me-2"></i>Comment restaurer une sauvegarde ?
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center
                             justify-content-center mb-2" style="width:50px;height:50px;">
                    <span class="fw-bold text-primary">1</span>
                </div>
                <div class="small fw-semibold">Téléchargez</div>
                <div class="small text-muted">le fichier <code>.sql</code> de votre choix</div>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center
                             justify-content-center mb-2" style="width:50px;height:50px;">
                    <span class="fw-bold text-primary">2</span>
                </div>
                <div class="small fw-semibold">Ouvrez phpMyAdmin</div>
                <div class="small text-muted">et sélectionnez la base <code>pharmasys_db</code></div>
            </div>
            <div class="col-md-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center
                             justify-content-center mb-2" style="width:50px;height:50px;">
                    <span class="fw-bold text-primary">3</span>
                </div>
                <div class="small fw-semibold">Importez</div>
                <div class="small text-muted">Onglet "Importer" → choisir le fichier → Exécuter</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmerSuppression = function(nom) {
        document.getElementById('nomFichierSuppr').textContent  = nom;
        document.getElementById('inputFichierSuppr').value      = nom;
        new bootstrap.Modal(document.getElementById('modalSupprimer')).show();
    };
});
</script>
