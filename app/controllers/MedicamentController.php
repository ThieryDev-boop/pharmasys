<?php
// app/controllers/MedicamentController.php

require_once APP_PATH . '/models/MedicamentModel.php';

class MedicamentController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        $this->model = new MedicamentModel();
    }

    // Liste des medicaments
    public function index(): void {
        $page    = max(1, (int)($_GET['page']   ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $perPage = 20;

        $medicaments = $this->model->findAll($page, $perPage, $search);
        $total       = $this->model->countAll($search);
        $totalPages  = max(1, ceil($total / $perPage));

        $this->renderLayout('main', 'medicaments/index', compact(
            'medicaments', 'page', 'totalPages', 'total', 'search'
        ));
    }

    // Formulaire creation
    public function createForm(): void {
        Auth::requireRole('administrateur', 'pharmacien');
        $categories = $this->model->getCategories();
        $csrfToken  = Auth::generateCsrfToken();
        $errors     = [];
        $old        = [];
        $this->renderLayout('main', 'medicaments/form', compact(
            'categories', 'csrfToken', 'errors', 'old'
        ));
    }

    // Traitement creation
    public function create(): void {
        Auth::requireRole('administrateur', 'pharmacien');
        $this->requirePost();

        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $data   = $this->validerFormulaire($_POST);
        $errors = $data['errors'];
        $values = $data['values'];

        if (!empty($errors)) {
            $categories = $this->model->getCategories();
            $csrfToken  = Auth::generateCsrfToken();
            $old        = $_POST;
            $this->renderLayout('main', 'medicaments/form', compact(
                'categories', 'csrfToken', 'errors', 'old'
            ));
            return;
        }

        $id = $this->model->create($values);
        $this->redirect(BASE_URL . '/medicaments?success=cree');
    }

    // Formulaire edition
    public function editForm(): void {
        Auth::requireRole('administrateur', 'pharmacien');
        $id  = (int)($_GET['id'] ?? 0);
        $med = $this->model->findById($id);

        if (!$med) {
            $this->redirect(BASE_URL . '/medicaments');
        }

        $lots       = $this->model->getLots($id);
        $categories = $this->model->getCategories();
        $csrfToken  = Auth::generateCsrfToken();
        $errors     = [];
        $old        = $med;

        $this->renderLayout('main', 'medicaments/form', compact(
            'med', 'lots', 'categories', 'csrfToken', 'errors', 'old'
        ));
    }

    // Traitement edition
    public function edit(): void {
        Auth::requireRole('administrateur', 'pharmacien');
        $this->requirePost();

        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $id   = (int)($_POST['id_medicament'] ?? 0);
        $data = $this->validerFormulaire($_POST);

        if (!empty($data['errors'])) {
            $med        = $this->model->findById($id);
            $lots       = $this->model->getLots($id);
            $categories = $this->model->getCategories();
            $csrfToken  = Auth::generateCsrfToken();
            $errors     = $data['errors'];
            $old        = $_POST;
            $this->renderLayout('main', 'medicaments/form', compact(
                'med', 'lots', 'categories', 'csrfToken', 'errors', 'old'
            ));
            return;
        }

        $this->model->update($id, $data['values']);
        $this->redirect(BASE_URL . '/medicaments?success=modifie');
    }

    // Archiver un medicament
    public function delete(): void {
        Auth::requireRole('administrateur', 'pharmacien');
        $this->requirePost();

        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $id = (int)($_POST['id_medicament'] ?? 0);
        $this->model->archive($id);
        $this->redirect(BASE_URL . '/medicaments?success=archive');
    }

    // Validation du formulaire
    private function validerFormulaire(array $post): array {
        $errors = [];
        $values = [];

        $values['nom_commercial'] = trim($post['nom_commercial'] ?? '');
        if (empty($values['nom_commercial'])) {
            $errors['nom_commercial'] = 'Le nom commercial est obligatoire.';
        }

        $values['prix_vente'] = (float)($post['prix_vente'] ?? 0);
        if ($values['prix_vente'] <= 0) {
            $errors['prix_vente'] = 'Le prix de vente doit etre superieur a 0.';
        }

        $values['dci']               = trim($post['dci']             ?? '');
        $values['forme_galenique']   = trim($post['forme_galenique'] ?? '');
        $values['dosage']            = trim($post['dosage']          ?? '');
        $values['id_categorie']      = (int)($post['id_categorie']   ?? 0);
        $values['conditionnement']   = max(1, (int)($post['conditionnement'] ?? 1));
        $values['code_barres']       = trim($post['code_barres']     ?? '');
        $values['prix_achat']        = (float)($post['prix_achat']   ?? 0);
        $values['tva']               = (float)($post['tva']          ?? 0);
        $values['seuil_minimum']     = max(0, (int)($post['seuil_minimum'] ?? 5));
        $values['ordonnance_requise']= isset($post['ordonnance_requise']) ? 1 : 0;

        return compact('errors', 'values');
    }
}
