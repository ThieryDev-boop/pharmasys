<?php // app/views/commandes/index.php

$statutConfig = [
    ''                   => ['label' => 'Toutes',       'color' => 'secondary'],
    'brouillon'          => ['label' => 'Brouillon',    'color' => 'secondary'],
    'envoye'             => ['label' => 'Envoyée',      'color' => 'primary'],
    'recu_partiellement' => ['label' => 'Partielle',    'color' => 'warning'],
    'recu_totalement'               => ['label' => 'Reçue',        'color' => 'success'],
    'annule'             => ['label' => 'Annulée',      'color' => 'danger'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-clipboard-check text-primary me-2"></i>Commandes fournisseurs
    </h4>
    <a href="<?= BASE_URL ?>/commandes/create" class="btn btn-primary">
        <i class="bi bi-clipboard-plus me-1"></i>Nouvelle commande
    </a>
</div>

<?php if (!empty($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>
    <?= $_GET['success'] === 'annule' ? 'Commande annulée.' : 'Opération réussie.' ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filtres statut -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php foreach ($statutConfig as $val => $sc): ?>
            <a href="<?= BASE_URL ?>/commandes<?= $val ? '?statut=' . $val : '' ?>"
               class="btn btn-sm <?= $statut === $val ? 'btn-' . $sc['color'] : 'btn-outline-' . $sc['color'] ?>">
                <?= $sc['label'] ?>
                <?php if ($val && !empty($compteurs[$val])): ?>
                <span class="badge bg-white text-<?= $sc['color'] ?> ms-1">
                    <?= $compteurs[$val] ?>
                </span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>

            <!-- Recherche -->
            <form method="GET" action="<?= BASE_URL ?>/commandes"
                  class="d-flex gap-2 ms-auto">
                <?php if ($statut): ?>
                <input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>">
                <?php endif; ?>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="N° commande ou fournisseur..."
                    value="<?= htmlspecialchars($search) ?>" style="width:240px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search): ?>
                <a href="<?= BASE_URL ?>/commandes<?= $statut ? '?statut=' . $statut : '' ?>"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">N° Commande</th>
                    <th>Fournisseur</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Livraison prévue</th>
                    <th class="text-end">Montant</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($commandes)): ?>
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-clipboard fs-2 d-block mb-2 opacity-25"></i>
                    Aucune commande trouvée.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($commandes as $c):
                $sc = $statutConfig[$c['statut']] ?? ['label' => $c['statut'], 'color' => 'secondary'];
            ?>
            <tr>
                <td class="ps-3">
                    <a href="<?= BASE_URL ?>/commandes/detail?id=<?= $c['id_commande'] ?>"
                       class="fw-semibold text-decoration-none text-primary">
                        <?= htmlspecialchars($c['numero_commande']) ?>
                    </a>
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($c['raison_sociale']) ?></td>
                <td class="text-center small text-muted">
                    <?= date('d/m/Y', strtotime($c['date_commande'])) ?>
                </td>
                <td class="text-center small <?= $c['date_livraison_prevue'] && strtotime($c['date_livraison_prevue']) < time() && !in_array($c['statut'], ['recu','annule']) ? 'text-danger fw-semibold' : 'text-muted' ?>">
                    <?= $c['date_livraison_prevue']
                        ? date('d/m/Y', strtotime($c['date_livraison_prevue']))
                        : '—' ?>
                </td>
                <td class="text-end fw-semibold">
                    <?= number_format($c['montant_total'], 0, ',', ' ') ?> F
                </td>
                <td class="text-center">
                    <span class="badge bg-<?= $sc['color'] ?>"><?= $sc['label'] ?></span>
                </td>
                <td class="text-center pe-3">
                    <a href="<?= BASE_URL ?>/commandes/detail?id=<?= $c['id_commande'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
