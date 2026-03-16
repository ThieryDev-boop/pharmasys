<?php // app/views/rapports/stock.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-clipboard-data text-info me-2"></i>Etat du stock
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/rapports/stock?export=pdf" target="_blank"
           class="btn btn-sm btn-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="<?= BASE_URL ?>/rapports/stock?export=csv"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
        </a>
        <a href="<?= BASE_URL ?>/rapports" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- Valeur du stock -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">References actives</div>
            <div class="fw-bold fs-3 text-primary"><?= $valeur['nb_references'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Total unites</div>
            <div class="fw-bold fs-3 text-info">
                <?= number_format($valeur['total_unites'] ?? 0, 0, ',', ' ') ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Valeur achat</div>
            <div class="fw-bold fs-5 text-warning">
                <?= number_format($valeur['valeur_achat'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small">Valeur vente</div>
            <div class="fw-bold fs-5 text-success">
                <?= number_format($valeur['valeur_vente'] ?? 0, 0, ',', ' ') ?> F
            </div>
        </div>
    </div>
</div>

<!-- Alertes rupture -->
<?php if (!empty($alertes)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-danger text-white fw-semibold py-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Alertes stock (<?= count($alertes) ?> medicament(s))
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Medicament</th>
                    <th>DCI</th>
                    <th class="text-center">Stock actuel</th>
                    <th class="text-center">Seuil minimum</th>
                    <th class="text-center">Etat</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($alertes as $a): ?>
            <tr>
                <td class="ps-3 fw-semibold small"><?= htmlspecialchars($a['nom_commercial']) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($a['dci'] ?? '') ?></td>
                <td class="text-center">
                    <span class="fw-bold <?= $a['stock_total'] == 0 ? 'text-danger' : 'text-warning' ?>">
                        <?= $a['stock_total'] ?>
                    </span>
                </td>
                <td class="text-center text-muted"><?= $a['seuil_minimum'] ?></td>
                <td class="text-center">
                    <span class="badge bg-<?= $a['type_alerte'] === 'rupture' ? 'danger' : 'warning' ?>">
                        <?= $a['type_alerte'] === 'rupture' ? 'Rupture' : 'Alerte' ?>
                    </span>
                </td>
                <td class="text-center pe-3">
                    <a href="<?= BASE_URL ?>/commandes/create" class="btn btn-xs btn-outline-warning btn-sm">
                        <i class="bi bi-clipboard-plus me-1"></i>Commander
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Lots proches peremption -->
<?php if (!empty($peremptions)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark fw-semibold py-3">
        <i class="bi bi-calendar-x-fill me-2"></i>
        Lots proches de peremption — 90 jours (<?= count($peremptions) ?>)
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Medicament</th>
                    <th>N° Lot</th>
                    <th class="text-center">Date peremption</th>
                    <th class="text-center">Jours restants</th>
                    <th class="text-center">Qte restante</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($peremptions as $p): ?>
            <?php $couleur = $p['jours_restants'] <= 30 ? 'danger' : 'warning'; ?>
            <tr>
                <td class="ps-3 small fw-semibold"><?= htmlspecialchars($p['nom_commercial']) ?></td>
                <td class="small"><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['numero_lot']) ?></span></td>
                <td class="text-center small">
                    <?= date('d/m/Y', strtotime($p['date_peremption'])) ?>
                </td>
                <td class="text-center">
                    <span class="badge bg-<?= $couleur ?>"><?= $p['jours_restants'] ?> j</span>
                </td>
                <td class="text-center fw-bold"><?= $p['quantite_restante'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Etat complet stock -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table text-info me-2"></i>Inventaire complet</span>
        <span class="badge bg-secondary"><?= count($etatStock) ?> references</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Medicament</th>
                        <th>Categorie</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Seuil</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end">Prix vente</th>
                        <th class="text-center pe-3">Prochain peremption</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($etatStock as $m):
                    $stock = (int)$m['stock_total'];
                    $seuil = (int)$m['seuil_minimum'];
                    if ($stock == 0)          { $sc = 'danger';    $sl = 'Rupture'; }
                    elseif ($stock <= $seuil) { $sc = 'warning';   $sl = 'Alerte'; }
                    else                      { $sc = 'success';   $sl = 'OK'; }
                ?>
                <tr>
                    <td class="ps-3 small fw-semibold"><?= htmlspecialchars($m['nom_commercial']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($m['nom_categorie'] ?? '—') ?></td>
                    <td class="text-center fw-bold text-<?= $sc ?>"><?= $stock ?></td>
                    <td class="text-center text-muted small"><?= $seuil ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $sc ?>"><?= $sl ?></span>
                    </td>
                    <td class="text-end small"><?= number_format($m['prix_vente'], 0, ',', ' ') ?> F</td>
                    <td class="text-center small text-muted pe-3">
                        <?= $m['prochaine_peremption']
                            ? date('d/m/Y', strtotime($m['prochaine_peremption']))
                            : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
