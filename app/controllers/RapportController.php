<?php
// app/controllers/RapportController.php

require_once APP_PATH . '/models/RapportModel.php';

class RapportController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        $this->model = new RapportModel();
    }

    // Page d'accueil des rapports
    public function index(): void {
        // Donnees pour les stats rapides
        $aujourd_hui  = date('Y-m-d');
        $debut_mois   = date('Y-m-01');
        $debut_annee  = date('Y-01-01');

        $stats_jour   = $this->model->getStatsVentes($aujourd_hui, $aujourd_hui);
        $stats_mois   = $this->model->getStatsVentes($debut_mois, $aujourd_hui);
        $stats_annee  = $this->model->getStatsVentes($debut_annee, $aujourd_hui);
        $valeur_stock = $this->model->getValeurStock();
        $alertes      = $this->model->getAlertesStock();
        $peremptions  = $this->model->getLotsPeremption(30);

        $this->renderLayout('main', 'rapports/index', compact(
            'stats_jour', 'stats_mois', 'stats_annee',
            'valeur_stock', 'alertes', 'peremptions'
        ));
    }

    // Rapport ventes (affichage + export)
    public function ventes(): void {
        $dateDebut = $_GET['date_debut'] ?? date('Y-m-01');
        $dateFin   = $_GET['date_fin']   ?? date('Y-m-d');
        $export    = $_GET['export']     ?? '';

        $stats     = $this->model->getStatsVentes($dateDebut, $dateFin);
        $ventes    = $this->model->getVentesPeriode($dateDebut, $dateFin);
        $topMeds   = $this->model->getTopMedicaments($dateDebut, $dateFin);
        $parJour   = $this->model->getVentesParJour($dateDebut, $dateFin);
        $parCaissier = $this->model->getVentesParCaissier($dateDebut, $dateFin);

        if ($export === 'pdf') {
            $this->exportVentesPdf($dateDebut, $dateFin, $stats, $ventes, $topMeds, $parCaissier);
            return;
        }
        if ($export === 'csv') {
            $this->exportVentesCsv($dateDebut, $dateFin, $ventes);
            return;
        }

        $this->renderLayout('main', 'rapports/ventes', compact(
            'dateDebut', 'dateFin', 'stats', 'ventes', 'topMeds', 'parJour', 'parCaissier'
        ));
    }

    // Rapport stock
    public function stock(): void {
        $export    = $_GET['export'] ?? '';
        $filtre    = $_GET['filtre'] ?? 'tous';

        $etatStock   = $this->model->getEtatStock();
        $alertes     = $this->model->getAlertesStock();
        $peremptions = $this->model->getLotsPeremption(90);
        $valeur      = $this->model->getValeurStock();

        if ($export === 'pdf') {
            $this->exportStockPdf($etatStock, $alertes, $peremptions, $valeur);
            return;
        }
        if ($export === 'csv') {
            $this->exportStockCsv($etatStock);
            return;
        }

        $this->renderLayout('main', 'rapports/stock', compact(
            'etatStock', 'alertes', 'peremptions', 'valeur', 'filtre'
        ));
    }

    // Rapport fournisseurs
    public function fournisseurs(): void {
        $statsFournisseurs = $this->model->getStatsFournisseurs();
        $export = $_GET['export'] ?? '';

        if ($export === 'pdf') {
            $this->exportFournisseursPdf($statsFournisseurs);
            return;
        }

        $this->renderLayout('main', 'rapports/fournisseurs', compact('statsFournisseurs'));
    }

    // Rapport clients
    public function clients(): void {
        $topClients = $this->model->getTopClients(20);
        $export = $_GET['export'] ?? '';

        if ($export === 'pdf') {
            $this->exportClientsPdf($topClients);
            return;
        }

        $this->renderLayout('main', 'rapports/clients', compact('topClients'));
    }

    // ── EXPORTS PDF ──────────────────────────────────────────

    private function exportVentesPdf(string $d1, string $d2, array $stats, array $ventes, array $topMeds, array $parCaissier): void {
        header('Content-Type: text/html; charset=utf-8');
        // On genere une page HTML dediee a l'impression
        $titre = 'Rapport des ventes du ' . date('d/m/Y', strtotime($d1))
               . ' au ' . date('d/m/Y', strtotime($d2));
        require APP_PATH . '/views/rapports/pdf/ventes.php';
        exit;
    }

    private function exportStockPdf(array $etat, array $alertes, array $peremptions, $valeur): void {
        header('Content-Type: text/html; charset=utf-8');
        require APP_PATH . '/views/rapports/pdf/stock.php';
        exit;
    }

    private function exportFournisseursPdf(array $stats): void {
        header('Content-Type: text/html; charset=utf-8');
        require APP_PATH . '/views/rapports/pdf/fournisseurs.php';
        exit;
    }

    private function exportClientsPdf(array $clients): void {
        header('Content-Type: text/html; charset=utf-8');
        require APP_PATH . '/views/rapports/pdf/clients.php';
        exit;
    }

    // ── EXPORTS CSV ──────────────────────────────────────────

    private function exportVentesCsv(string $d1, string $d2, array $ventes): void {
        $filename = 'ventes_' . $d1 . '_' . $d2 . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($out, ['N° Facture', 'Date', 'Client', 'Caissier', 'Total TTC', 'Paye', 'Monnaie'], ';');
        foreach ($ventes as $v) {
            fputcsv($out, [
                $v['numero_facture'],
                date('d/m/Y H:i', strtotime($v['date_vente'])),
                $v['client_nom'],
                $v['caissier_nom'],
                $v['montant_total_ttc'],
                $v['montant_paye'],
                $v['monnaie_rendue'],
            ], ';');
        }
        fclose($out);
        exit;
    }

    private function exportStockCsv(array $etat): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stock_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['Medicament', 'DCI', 'Categorie', 'Stock total', 'Seuil minimum', 'Prix vente', 'Prochaine peremption'], ';');
        foreach ($etat as $m) {
            fputcsv($out, [
                $m['nom_commercial'],
                $m['dci'] ?? '',
                $m['nom_categorie'] ?? '',
                $m['stock_total'],
                $m['seuil_minimum'],
                $m['prix_vente'],
                $m['prochaine_peremption'] ? date('d/m/Y', strtotime($m['prochaine_peremption'])) : '',
            ], ';');
        }
        fclose($out);
        exit;
    }
}
