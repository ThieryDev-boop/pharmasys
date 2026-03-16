<?php
// app/controllers/LotController.php

require_once APP_PATH . '/models/LotModel.php';
require_once APP_PATH . '/models/MedicamentModel.php';

class LotController extends Controller {

    private $lotModel;
    private $medModel;

    public function __construct() {
        Auth::requireAuth();
        Auth::requireRole('administrateur', 'pharmacien');
        $this->lotModel = new LotModel();
        $this->medModel = new MedicamentModel();
    }

    // Liste de tous les lots
    public function index(): void {
        $filtre  = trim($_GET['filtre'] ?? 'tous');
        $search  = trim($_GET['search'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $lots       = $this->lotModel->findAll($filtre, $search, $page, $perPage);
        $total      = $this->lotModel->countAll($filtre, $search);
        $totalPages = max(1, ceil($total / $perPage));
        $alertes    = $this->lotModel->getAlertesPeremption(90);

        $this->renderLayout('main', 'lots/index', compact(
            'lots', 'total', 'page', 'totalPages', 'filtre', 'search', 'alertes'
        ));
    }

    // Formulaire ajout lot
    public function createForm(): void {
        $idMed = (int)($_GET['id_medicament'] ?? 0);
        $medicaments = $this->medModel->findAll(1, 1000);
        $med         = $idMed ? $this->medModel->findById($idMed) : null;
        $csrfToken   = Auth::generateCsrfToken();
        $errors      = [];
        $old         = ['id_medicament' => $idMed];

        $this->renderLayout('main', 'lots/form', compact(
            'medicaments', 'med', 'csrfToken', 'errors', 'old'
        ));
    }

    // Traitement ajout lot
    public function create(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $errors = $this->valider($_POST);

        if (!empty($errors)) {
            $medicaments = $this->medModel->findAll(1, 1000);
            $med         = $this->medModel->findById((int)($_POST['id_medicament'] ?? 0));
            $csrfToken   = Auth::generateCsrfToken();
            $old         = $_POST;
            $this->renderLayout('main', 'lots/form', compact(
                'medicaments', 'med', 'csrfToken', 'errors', 'old'
            ));
            return;
        }

        $this->lotModel->create([
            'id_medicament'   => (int)$_POST['id_medicament'],
            'numero_lot'      => trim($_POST['numero_lot']),
            'date_fabrication'=> $_POST['date_fabrication'] ?? null,
            'date_peremption' => $_POST['date_peremption'],
            'quantite'        => (int)$_POST['quantite'],
            'prix_achat'      => (float)($_POST['prix_achat'] ?? 0),
        ]);

        $redirect = !empty($_POST['id_medicament'])
            ? BASE_URL . '/medicaments/edit?id=' . (int)$_POST['id_medicament'] . '&success=lot_ajoute'
            : BASE_URL . '/lots?success=cree';

        $this->redirect($redirect);
    }

    // Validation
    private function valider(array $post): array {
        $errors = [];
        if (empty($post['id_medicament'])) {
            $errors['id_medicament'] = 'Selectionnez un medicament.';
        }
        if (empty($post['numero_lot'])) {
            $errors['numero_lot'] = 'Le numero de lot est obligatoire.';
        }
        if (empty($post['date_peremption'])) {
            $errors['date_peremption'] = 'La date de peremption est obligatoire.';
        } elseif ($post['date_peremption'] <= date('Y-m-d')) {
            $errors['date_peremption'] = 'La date de peremption doit etre dans le futur.';
        }
        if (empty($post['quantite']) || (int)$post['quantite'] <= 0) {
            $errors['quantite'] = 'La quantite doit etre superieure a 0.';
        }
        return $errors;
    }
}
