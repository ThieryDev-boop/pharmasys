<?php
// app/controllers/FournisseurController.php

require_once APP_PATH . '/models/FournisseurModel.php';

class FournisseurController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        Auth::requireRole('administrateur', 'pharmacien');
        $this->model = new FournisseurModel();
    }

    // Liste avec modale integree
    public function index(): void {
        $page    = max(1, (int)($_GET['page']   ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $perPage = 20;

        $fournisseurs = $this->model->findAll($page, $perPage, $search);
        $total        = $this->model->countAll($search);
        $totalPages   = max(1, ceil($total / $perPage));
        $csrfToken    = Auth::generateCsrfToken();

        $this->renderLayout('main', 'fournisseurs/index', compact(
            'fournisseurs', 'total', 'page', 'totalPages', 'search', 'csrfToken'
        ));
    }

    // Creation (POST depuis modale)
    public function create(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $errors = $this->valider($_POST);
        if (!empty($errors)) {
            // Retour avec erreurs en session
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            $this->redirect(BASE_URL . '/fournisseurs?erreur=1');
            return;
        }

        $this->model->create([
            'raison_sociale' => trim($_POST['raison_sociale']),
            'contact'        => trim($_POST['contact']   ?? ''),
            'telephone'      => trim($_POST['telephone'] ?? ''),
            'email'          => trim($_POST['email']     ?? ''),
            'adresse'        => trim($_POST['adresse']   ?? ''),
        ]);

        $this->redirect(BASE_URL . '/fournisseurs?success=cree');
    }

    // Edition (POST depuis modale)
    public function edit(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id     = (int)($_POST['id_fournisseur'] ?? 0);
        $errors = $this->valider($_POST);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            $this->redirect(BASE_URL . '/fournisseurs?erreur=1');
            return;
        }

        $this->model->update($id, [
            'raison_sociale' => trim($_POST['raison_sociale']),
            'contact'        => trim($_POST['contact']   ?? ''),
            'telephone'      => trim($_POST['telephone'] ?? ''),
            'email'          => trim($_POST['email']     ?? ''),
            'adresse'        => trim($_POST['adresse']   ?? ''),
        ]);

        $this->redirect(BASE_URL . '/fournisseurs?success=modifie');
    }

    // Desactiver un fournisseur
    public function desactiver(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');
        $id = (int)($_POST['id_fournisseur'] ?? 0);
        $this->model->toggleActif($id);
        $this->redirect(BASE_URL . '/fournisseurs?success=modifie');
    }

    // Fiche detail d'un fournisseur
    public function detail(): void {
        $id          = (int)($_GET['id'] ?? 0);
        $fournisseur = $this->model->findById($id);
        if (!$fournisseur) $this->redirect(BASE_URL . '/fournisseurs');

        $commandes = $this->model->getCommandes($id);
        $stats     = $this->model->getStats($id);
        $csrfToken = Auth::generateCsrfToken();

        $this->renderLayout('main', 'fournisseurs/detail', compact(
            'fournisseur', 'commandes', 'stats', 'csrfToken'
        ));
    }

    // Validation
    private function valider(array $post): array {
        $errors = [];
        if (empty(trim($post['raison_sociale'] ?? ''))) {
            $errors['raison_sociale'] = 'La raison sociale est obligatoire.';
        }
        if (!empty($post['email']) && !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }
        return $errors;
    }
}
