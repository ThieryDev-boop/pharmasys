<?php
// app/controllers/ClientController.php

require_once APP_PATH . '/models/ClientModel.php';

class ClientController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        $this->model = new ClientModel();
    }

    // Liste des clients
    public function index(): void {
        $page    = max(1, (int)($_GET['page']   ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $perPage = 20;

        $clients    = $this->model->findAllPagine($page, $perPage, $search);
        $total      = $this->model->countAll($search);
        $totalPages = max(1, ceil($total / $perPage));

        $this->renderLayout('main', 'clients/index', compact(
            'clients', 'total', 'page', 'totalPages', 'search'
        ));
    }

    // Formulaire creation
    public function createForm(): void {
        $csrfToken = Auth::generateCsrfToken();
        $errors    = [];
        $old       = [];
        $this->renderLayout('main', 'clients/form', compact('csrfToken', 'errors', 'old'));
    }

    // Traitement creation
    public function create(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $errors = $this->valider($_POST);

        if (!empty($errors)) {
            $csrfToken = Auth::generateCsrfToken();
            $old       = $_POST;
            $this->renderLayout('main', 'clients/form', compact('csrfToken', 'errors', 'old'));
            return;
        }

        $this->model->create([
            'nom'       => trim($_POST['nom']),
            'prenom'    => trim($_POST['prenom']    ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'adresse'   => trim($_POST['adresse']   ?? ''),
        ]);

        $this->redirect(BASE_URL . '/clients?success=cree');
    }

    // Formulaire edition
    public function editForm(): void {
        $id     = (int)($_GET['id'] ?? 0);
        $client = $this->model->findById($id);
        if (!$client) $this->redirect(BASE_URL . '/clients');

        $csrfToken = Auth::generateCsrfToken();
        $errors    = [];
        $old       = $client;
        $this->renderLayout('main', 'clients/form', compact('client', 'csrfToken', 'errors', 'old'));
    }

    // Traitement edition
    public function edit(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }

        $id     = (int)($_POST['id_client'] ?? 0);
        $errors = $this->valider($_POST);

        if (!empty($errors)) {
            $client    = $this->model->findById($id);
            $csrfToken = Auth::generateCsrfToken();
            $old       = $_POST;
            $this->renderLayout('main', 'clients/form', compact('client', 'csrfToken', 'errors', 'old'));
            return;
        }

        $this->model->update($id, [
            'nom'       => trim($_POST['nom']),
            'prenom'    => trim($_POST['prenom']    ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'adresse'   => trim($_POST['adresse']   ?? ''),
        ]);

        $this->redirect(BASE_URL . '/clients?success=modifie');
    }

    // Historique achats d'un client
    public function historique(): void {
        $id     = (int)($_GET['id'] ?? 0);
        $client = $this->model->findById($id);
        if (!$client) $this->redirect(BASE_URL . '/clients');

        $achats = $this->model->getHistoriqueAchats($id);
        $stats  = $this->model->getStats($id);

        $this->renderLayout('main', 'clients/historique', compact('client', 'achats', 'stats'));
    }

    // Desactiver un client
    public function desactiver(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide.');
        }
        Auth::requireRole('administrateur', 'pharmacien');
        $id = (int)($_POST['id_client'] ?? 0);
        $this->model->toggleActif($id);
        $this->redirect(BASE_URL . '/clients?success=modifie');
    }

    // Recherche AJAX (pour la vente)
    public function rechercher(): void {
        header('Content-Type: application/json');
        $terme = trim($_GET['q'] ?? '');
        if (strlen($terme) < 2) {
            echo json_encode([]); return;
        }
        echo json_encode($this->model->search($terme));
    }

    // Validation
    private function valider(array $post): array {
        $errors = [];
        if (empty(trim($post['nom'] ?? ''))) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (!empty($post['telephone'])) {
            $tel = preg_replace('/\s+/', '', $post['telephone']);
            if (!preg_match('/^[+\d]{8,15}$/', $tel)) {
                $errors['telephone'] = 'Numero de telephone invalide.';
            }
        }
        return $errors;
    }
}
