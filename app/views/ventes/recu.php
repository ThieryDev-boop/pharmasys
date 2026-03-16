<?php // app/views/ventes/recu.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-receipt text-success me-2"></i>Recu de vente
    </h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
        <a href="<?= BASE_URL ?>/ventes/create" class="btn btn-success">
            <i class="bi bi-cart-plus me-1"></i>Nouvelle vente
        </a>
        <a href="<?= BASE_URL ?>/ventes" class="btn btn-outline-secondary">
            <i class="bi bi-list me-1"></i>Historique
        </a>
    </div>
</div>

<!-- Recu imprimable -->
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card border shadow" id="recuCard">
    <div class="card-body p-4">

        <!-- En-tete pharmacie -->
        <div class="text-center mb-4 pb-3 border-bottom border-2">
            <h4 class="fw-bold text-primary mb-0">PHARMACIE Pharmasys</h4>
            <p class="text-muted small mb-0">Adresse : Rue CN5054, DOUALA | Tel : +237 659877980</p>
            <p class="text-muted small mb-0">RCCM : CCMR007/877004 | NIF : Pharma00077701</p>
        </div>

        <!-- Infos facture -->
        <div class="row mb-3">
            <div class="col-6">
                <p class="mb-1"><strong>Facture N° :</strong>
                    <span class="text-primary fw-bold">
                        <?= htmlspecialchars($vente['numero_facture']) ?>
                    </span>
                </p>
                <p class="mb-1"><strong>Date :</strong>
                    <?= date('d/m/Y H:i', strtotime($vente['date_vente'])) ?>
                </p>
                <p class="mb-0"><strong>Caissier :</strong>
                    <?= htmlspecialchars($vente['caissier_nom']) ?>
                </p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1"><strong>Client :</strong>
                    <?= trim($vente['client_nom']) ? htmlspecialchars(trim($vente['client_nom'])) : 'Passage' ?>
                </p>
                <?php if ($vente['client_telephone']): ?>
                <p class="mb-0"><strong>Tel :</strong>
                    <?= htmlspecialchars($vente['client_telephone']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tableau des lignes -->
        <table class="table table-sm table-bordered mb-3">
            <thead class="table-dark">
                <tr>
                    <th>Medicament</th>
                    <th class="text-center">Lot</th>
                    <th class="text-center">Qte</th>
                    <th class="text-end">P.U.</th>
                    <?php if (array_sum(array_column($lignes, 'remise')) > 0): ?>
                    <th class="text-center">Remise</th>
                    <?php endif; ?>
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td>
                    <div class="fw-semibold small"><?= htmlspecialchars($l['nom_commercial']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($l['dci'] ?? '') ?></small>
                </td>
                <td class="text-center small"><?= htmlspecialchars($l['numero_lot']) ?></td>
                <td class="text-center"><?= $l['quantite'] ?></td>
                <td class="text-end small"><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> F</td>
                <?php if (array_sum(array_column($lignes, 'remise')) > 0): ?>
                <td class="text-center small">
                    <?= $l['remise'] > 0 ? $l['remise'] . '%' : '—' ?>
                </td>
                <?php endif; ?>
                <td class="text-end fw-semibold"><?= number_format($l['montant_ligne'], 0, ',', ' ') ?> F</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="row justify-content-end">
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr class="border-top border-2">
                        <td class="fw-bold fs-6">TOTAL TTC</td>
                        <td class="text-end fw-bold fs-6 text-primary">
                            <?= number_format($vente['montant_total_ttc'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Montant recu</td>
                        <td class="text-end">
                            <?= number_format($vente['montant_paye'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                    <tr>
                        <td class="text-success fw-semibold">Monnaie rendue</td>
                        <td class="text-end text-success fw-semibold">
                            <?= number_format($vente['monnaie_rendue'], 0, ',', ' ') ?> F
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Pied de recu -->
        <div class="text-center mt-4 pt-3 border-top">
            <p class="text-muted small mb-0">Merci de votre visite — Bonne sante !</p>
            <p class="text-muted small mb-0">
                Les medicaments vendus ne sont ni repris ni echanges.
            </p>
        </div>

    </div>
</div>
</div>
</div>

<style>
@media print {
    .sidebar, .topbar, .d-flex.justify-content-between, nav { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-body { padding: 0 !important; }
    #recuCard { border: none !important; box-shadow: none !important; }
    .col-md-7 { width: 100% !important; max-width: 100% !important; }
}
</style>
