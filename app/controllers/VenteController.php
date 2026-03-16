<?php
// app/controllers/VenteController.php

require_once APP_PATH . '/models/VenteModel.php';
require_once APP_PATH . '/models/LotModel.php';
require_once APP_PATH . '/models/MedicamentModel.php';
require_once APP_PATH . '/models/ClientModel.php';

class VenteController extends Controller {

    private $venteModel;
    private $lotModel;
    private $medModel;
    private $clientModel;

    public function __construct() {
        Auth::requireAuth();
        $this->venteModel  = new VenteModel();
        $this->lotModel    = new LotModel();
        $this->medModel    = new MedicamentModel();
        $this->clientModel = new ClientModel();
    }

    // Historique des ventes
    public function index(): void {
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $search = trim($_GET['search'] ?? '');
        $ventes = $this->venteModel->findAllValidees($page, 20, $search);
        $total  = $this->venteModel->countAllValidees($search);
        $totalPages = max(1, ceil($total / 20));
        $this->renderLayout('main', 'ventes/index', compact('ventes', 'page', 'totalPages', 'total', 'search'));
    }

    // Afficher formulaire nouvelle vente
    public function createForm(): void {
        $clients   = $this->clientModel->findAll();
        $csrfToken = Auth::generateCsrfToken();
        $this->renderLayout('main', 'ventes/create', compact('clients', 'csrfToken'));
    }

    // Initialiser une vente (POST AJAX)
    public function init(): void {
        header('Content-Type: application/json');
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'CSRF invalide']); return;
        }
        $idClient = (int)($_POST['id_client'] ?? 0);
        $idVente  = $this->venteModel->creer(Auth::userId(), $idClient ?: null);
        echo json_encode(['success' => true, 'id_vente' => $idVente]);
    }

    // Rechercher un medicament (AJAX)
    public function rechercherMedicament(): void {
        header('Content-Type: application/json');
        $terme = trim($_GET['q'] ?? '');
        if (strlen($terme) < 2) {
            echo json_encode([]); return;
        }
        $resultats = $this->medModel->searchByNom($terme);
        echo json_encode($resultats);
    }

    // Ajouter une ligne a la vente (AJAX)
    public function ajouterLigne(): void {
        header('Content-Type: application/json');
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'CSRF invalide']); return;
        }

        $idVente = (int)($_POST['id_vente']      ?? 0);
        $idMed   = (int)($_POST['id_medicament'] ?? 0);
        $qte     = (int)($_POST['quantite']      ?? 0);
        $remise  = (float)($_POST['remise']      ?? 0);

        if (!$idVente || !$idMed || $qte <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parametres invalides']); return;
        }

        $med = $this->medModel->findById($idMed);
        if (!$med) {
            echo json_encode(['success' => false, 'message' => 'Medicament introuvable']); return;
        }

        // Verifier ordonnance
        if ($med['ordonnance_requise'] && empty($_POST['ordonnance_ok'])) {
            echo json_encode([
                'success'             => false,
                'requires_ordonnance' => true,
                'message'             => 'Ce medicament necessite une ordonnance.'
            ]); return;
        }

        // Selection FEFO
        $lotsFefo = $this->lotModel->getLotsFEFO($idMed, $qte);
        if (empty($lotsFefo)) {
            echo json_encode(['success' => false, 'message' => 'Stock insuffisant pour ce medicament.']); return;
        }

        // Ajouter les lignes et decrementer les lots
        foreach ($lotsFefo as $item) {
            $montant = round($med['prix_vente'] * $item['quantite'] * (1 - $remise / 100), 0);
            $this->venteModel->ajouterLigne([
                'id_vente'      => $idVente,
                'id_medicament' => $idMed,
                'id_lot'        => $item['lot']['id_lot'],
                'quantite'      => $item['quantite'],
                'prix_unitaire' => $med['prix_vente'],
                'remise'        => $remise,
                'montant_ligne' => $montant,
            ]);
            $this->lotModel->decrementer($item['lot']['id_lot'], $item['quantite']);
        }

        $this->venteModel->recalculerTotal($idVente);
        $vente  = $this->venteModel->findById($idVente);
        $lignes = $this->venteModel->getLignes($idVente);

        echo json_encode([
            'success'   => true,
            'lignes'    => $lignes,
            'total_ttc' => number_format($vente['montant_total_ttc'], 0, ',', ' '),
            'total_raw' => (float)$vente['montant_total_ttc'],
        ]);
    }

    // Supprimer une ligne (AJAX)
    public function supprimerLigne(): void {
        header('Content-Type: application/json');
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'CSRF invalide']); return;
        }

        $idLigne = (int)($_POST['id_ligne'] ?? 0);
        $idVente = (int)($_POST['id_vente'] ?? 0);

        $ligne = $this->venteModel->supprimerLigne($idLigne);
        if ($ligne) {
            // Restituer le stock
            $this->lotModel->restituer($ligne['id_lot'], $ligne['quantite']);
            $this->venteModel->recalculerTotal($idVente);
        }

        $vente  = $this->venteModel->findById($idVente);
        $lignes = $this->venteModel->getLignes($idVente);

        echo json_encode([
            'success'   => true,
            'lignes'    => $lignes,
            'total_ttc' => number_format($vente['montant_total_ttc'], 0, ',', ' '),
            'total_raw' => (float)$vente['montant_total_ttc'],
        ]);
    }

    // Valider la vente
    public function valider(): void {
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $idVente     = (int)($_POST['id_vente']     ?? 0);
        $montantPaye = (float)($_POST['montant_paye'] ?? 0);

        $vente = $this->venteModel->findById($idVente);
        if (!$vente || $vente['statut'] !== 'brouillon') {
            die('Vente invalide.');
        }
        if ($montantPaye < $vente['montant_total_ttc']) {
            // Montant insuffisant - retour formulaire
            $clients   = $this->clientModel->findAll();
            $csrfToken = Auth::generateCsrfToken();
            $lignes    = $this->venteModel->getLignes($idVente);
            $erreur    = 'Le montant paye est insuffisant.';
            $this->renderLayout('main', 'ventes/create',
                compact('clients', 'csrfToken', 'vente', 'lignes', 'erreur'));
            return;
        }

        $this->venteModel->finaliser($idVente, $montantPaye);
        $this->redirect(BASE_URL . '/ventes/recu?id=' . $idVente);
    }

    // Afficher/imprimer le recu
    public function imprimerRecu(): void {
        $idVente = (int)($_GET['id'] ?? 0);
        $vente   = $this->venteModel->findById($idVente);
        if (!$vente) $this->redirect(BASE_URL . '/ventes');
        $lignes  = $this->venteModel->getLignes($idVente);
        $this->renderLayout('main', 'ventes/recu', compact('vente', 'lignes'));
    }
}
