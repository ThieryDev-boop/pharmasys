<?php // app/views/commandes/detail.php

$statutConfig = [
    'brouillon'          => ['label' => 'Brouillon',          'color' => 'secondary', 'icon' => 'bi-pencil'],
    'envoye'             => ['label' => 'Envoyée',            'color' => 'primary',   'icon' => 'bi-send'],
    'recu_partiellement' => ['label' => 'Reçue partiellement','color' => 'warning',   'icon' => 'bi-box-seam'],
    'recu_totalement'               => ['label' => 'Reçue',              'color' => 'success',   'icon' => 'bi-check-circle'],
    'annule'             => ['label' => 'Annulée',            'color' => 'danger',    'icon' => 'bi-x-circle'],
];
$sc = $statutConfig[$commande['statut']] ?? ['label' => $commande['statut'], 'color' => 'secondary', 'icon' => 'bi-question'];

$peutEnvoyer  = $commande['statut'] === 'brouillon';
$peutRecevoir = in_array($commande['statut'], ['envoye', 'recu_partiellement']);
$peutAnnuler  = in_array($commande['statut'], ['brouillon', 'envoye']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clipboard-check text-primary me-2"></i>
            <?= htmlspecialchars($commande['numero_commande']) ?>
        </h4>
        <small class="text-muted">
            Créée le <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?>
            par <?= htmlspecialchars($commande['createur']) ?>
        </small>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-<?= $sc['color'] ?> fs-6 px-3 py-2">
            <i class="bi <?= $sc['icon'] ?> me-1"></i><?= $sc['label'] ?>
        </span>
        <a href="<?= BASE_URL ?>/commandes" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- Messages -->
<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?php
    $msgs = ['envoye' => 'Commande envoyée au fournisseur.', 'recu' => 'Réception enregistrée avec succès.'];
    echo $msgs[$_GET['success']] ?? 'Opération réussie.';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="row g-4">

    <!-- Infos commande -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-info-circle text-primary me-2"></i>Informations
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <div class="text-muted">Fournisseur</div>
                    <div class="fw-semibold"><?= htmlspecialchars($commande['raison_sociale']) ?></div>
                    <?php if ($commande['telephone']): ?>
                    <div class="text-muted"><?= htmlspecialchars($commande['telephone']) ?></div>
                    <?php endif; ?>
                </div>
                <hr class="my-2">
                <div class="mb-2">
                    <div class="text-muted">Date commande</div>
                    <div><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></div>
                </div>
                <?php if ($commande['date_livraison_prevue']): ?>
                <div class="mb-2">
                    <div class="text-muted">Livraison prévue</div>
                    <div class="<?= strtotime($commande['date_livraison_prevue']) < time() && $peutRecevoir ? 'text-danger fw-semibold' : '' ?>">
                        <?= date('d/m/Y', strtotime($commande['date_livraison_prevue'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($commande['note']): ?>
                <div class="mb-2">
                    <div class="text-muted">Note</div>
                    <div><?= nl2br(htmlspecialchars($commande['note'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total + Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="text-muted small mb-1">Total commande</div>
                <div class="fw-bold fs-2 text-primary mb-3">
                    <?= number_format($commande['montant_total'], 0, ',', ' ') ?> F
                </div>

                <?php if ($peutEnvoyer): ?>
                    <?php if (!empty($lignes)): ?>
                    <form method="POST" action="<?= BASE_URL ?>/commandes/envoyer">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                        <button type="submit" class="btn btn-warning w-100 mb-2"
                            onclick="return confirm('Envoyer cette commande ?')">
                            <i class="bi bi-send me-2"></i>Envoyer au fournisseur
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning py-2 small">
                        Ajoutez des médicaments avant d'envoyer.
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($peutRecevoir): ?>
                <button type="button" class="btn btn-success w-100 mb-2"
                    data-bs-toggle="modal" data-bs-target="#modalReception">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Enregistrer la réception
                </button>
                <?php endif; ?>

                <?php if ($commande['statut'] === 'recu'): ?>
                <div class="alert alert-success py-2 small mb-0">
                    <i class="bi bi-check-circle me-1"></i>Commande entièrement reçue.
                </div>
                <?php endif; ?>

                <?php if ($peutAnnuler): ?>
                <form method="POST" action="<?= BASE_URL ?>/commandes/annuler" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm"
                        onclick="return confirm('Annuler cette commande ?')">
                        <i class="bi bi-x-circle me-1"></i>Annuler la commande
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lignes -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between">
                <span><i class="bi bi-list-ul text-info me-2"></i>Articles commandés</span>
                <span class="badge bg-secondary"><?= count($lignes) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lignes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                    Aucun article.
                    <?php if ($peutEnvoyer): ?>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/commandes/create" class="btn btn-sm btn-outline-primary">
                            Modifier la commande
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Médicament</th>
                            <th class="text-center">Qté cmd</th>
                            <th class="text-center">Qté reçue</th>
                            <th class="text-end">Prix U.</th>
                            <th class="text-end pe-3">Montant</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lignes as $l):
                        $slConfig = [
                            'en_attente'     => ['label' => 'En attente',  'color' => 'secondary'],
                            'recue_partielle'=> ['label' => 'Partielle',   'color' => 'warning'],
                            'recue_totale'   => ['label' => 'Reçue',       'color' => 'success'],
                        ];
                        $sl = $slConfig[$l['statut_ligne']] ?? ['label' => $l['statut_ligne'], 'color' => 'secondary'];
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold small"><?= htmlspecialchars($l['nom_commercial']) ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($l['dci'] ?? '') ?>
                                <?= $l['forme_galenique'] ? ' — ' . htmlspecialchars($l['forme_galenique']) : '' ?>
                            </small>
                        </td>
                        <td class="text-center fw-bold"><?= $l['quantite_commandee'] ?></td>
                        <td class="text-center">
                            <span class="<?= $l['quantite_recue'] > 0 ? 'text-success fw-bold' : 'text-muted' ?>">
                                <?= $l['quantite_recue'] ?>
                            </span>
                        </td>
                        <td class="text-end small">
                            <?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F
                        </td>
                        <td class="text-end fw-semibold pe-3">
                            <?= number_format($l['quantite_commandee'] * $l['prix_unitaire'], 0, ',', ' ') ?> F
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $sl['color'] ?>"><?= $sl['label'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold pe-2">TOTAL</td>
                            <td class="text-end fw-bold pe-3 text-primary">
                                <?= number_format($commande['montant_total'], 0, ',', ' ') ?> F
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /row -->


<?php if ($peutRecevoir && !empty($lignes)): ?>
<!-- ============================================================ -->
<!-- MODALE RÉCEPTION                                             -->
<!-- ============================================================ -->
<div class="modal fade" id="modalReception" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-down me-2"></i>
                    Réception — <?= htmlspecialchars($commande['numero_commande']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/commandes/recevoir">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_commande" value="<?= $commande['id_commande'] ?>">

                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Pour chaque article reçu, renseignez la quantité et le lot.
                        Les articles avec quantité = 0 sont ignorés.
                    </div>

                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Médicament</th>
                                <th class="text-center">Qté cmd</th>
                                <th class="text-center" style="width:90px;">Qté reçue</th>
                                <th>N° Lot <span class="text-danger">*</span></th>
                                <th>Date péremption <span class="text-danger">*</span></th>
                                <th>Date fabrication</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lignes as $l):
                            if ($l['statut_ligne'] === 'recue_totale') continue;
                            $resteARecevoir = $l['quantite_commandee'] - $l['quantite_recue'];
                        ?>
                        <tr>
                            <td>
                                <input type="hidden"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][id_medicament]"
                                    value="<?= $l['id_medicament'] ?>">
                                <input type="hidden"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][prix_unitaire]"
                                    value="<?= $l['prix_unitaire'] ?>">
                                <div class="fw-semibold small"><?= htmlspecialchars($l['nom_commercial']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($l['dci'] ?? '') ?></small>
                            </td>
                            <td class="text-center"><?= $l['quantite_commandee'] ?></td>
                            <td class="text-center">
                                <input type="number"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][qte]"
                                    class="form-control form-control-sm text-center"
                                    value="<?= $resteARecevoir ?>"
                                    min="0" max="<?= $resteARecevoir ?>">
                            </td>
                            <td>
                                <input type="text"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][numero_lot]"
                                    class="form-control form-control-sm"
                                    placeholder="LOT-2024-001">
                            </td>
                            <td>
                                <input type="date"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][date_peremption]"
                                    class="form-control form-control-sm"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </td>
                            <td>
                                <input type="date"
                                    name="reception[<?= $l['id_ligne_cmd'] ?>][date_fabrication]"
                                    class="form-control form-control-sm">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-2"></i>Enregistrer la réception
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
