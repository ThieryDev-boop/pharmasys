<?php // app/views/clients/historique.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-clock-history text-primary me-2"></i>
        Historique — <?= htmlspecialchars($client['nom'] . ' ' . ($client['prenom'] ?? '')) ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/clients/edit?id=<?= $client['id_client'] ?>"
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <a href="<?= BASE_URL ?>/clients" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- Carte client + stats -->
<div class="row g-3 mb-4">

    <!-- Infos client -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary
                                d-flex align-items-center justify-content-center fw-bold"
                         style="width:50px;height:50px;font-size:1.1rem;">
                        <?= strtoupper(substr($client['nom'], 0, 1)) ?>
                        <?= strtoupper(substr($client['prenom'] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <?= htmlspecialchars($client['nom'] . ' ' . ($client['prenom'] ?? '')) ?>
                        </h5>
                        <small class="text-muted">
                            Client depuis <?= date('d/m/Y', strtotime($client['date_creation'])) ?>
                        </small>
                    </div>
                </div>
                <?php if ($client['telephone']): ?>
                <p class="mb-1 small">
                    <i class="bi bi-telephone text-muted me-2"></i>
                    <?= htmlspecialchars($client['telephone']) ?>
                </p>
                <?php endif; ?>
                <?php if ($client['adresse']): ?>
                <p class="mb-0 small">
                    <i class="bi bi-geo-alt text-muted me-2"></i>
                    <?= htmlspecialchars($client['adresse']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats achats -->
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-primary"><?= $stats['nb_achats'] ?? 0 ?></div>
                    <small class="text-muted">Achats total</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-success">
                        <?= number_format($stats['total_depense'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                    <small class="text-muted">Total depense</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-5 text-info">
                        <?= number_format($stats['panier_moyen'] ?? 0, 0, ',', ' ') ?> F
                    </div>
                    <small class="text-muted">Panier moyen</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-6 text-warning">
                        <?= $stats['dernier_achat']
                            ? date('d/m/Y', strtotime($stats['dernier_achat']))
                            : '—' ?>
                    </div>
                    <small class="text-muted">Dernier achat</small>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Historique des factures -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-receipt text-info me-2"></i>Factures
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">N° Facture</th>
                        <th>Date</th>
                        <th class="text-center">Articles</th>
                        <th class="text-end">Total TTC</th>
                        <th>Caissier</th>
                        <th class="text-center pe-3">Recu</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($achats)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aucun achat enregistre.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($achats as $a): ?>
                    <tr>
                        <td class="ps-3 fw-semibold text-primary">
                            <?= htmlspecialchars($a['numero_facture']) ?>
                        </td>
                        <td class="small">
                            <?= date('d/m/Y H:i', strtotime($a['date_vente'])) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <?= $a['nb_lignes'] ?> article(s)
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= number_format($a['montant_total_ttc'], 0, ',', ' ') ?> F
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($a['caissier_nom']) ?>
                        </td>
                        <td class="text-center pe-3">
                            <a href="<?= BASE_URL ?>/ventes/recu?id=<?= $a['id_vente'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Voir recu">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
