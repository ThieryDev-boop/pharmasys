<?php
// app/controllers/ProfilController.php

require_once APP_PATH . '/models/UtilisateurModel.php';

class ProfilController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        $this->model = new UtilisateurModel();
    }

    public function index(): void {
        $utilisateur = $this->model->findById(Auth::userId());
        $csrfToken   = Auth::generateCsrfToken();
        $success     = $_SESSION['profil_success'] ?? '';
        $errors      = $_SESSION['profil_errors']  ?? [];
        unset($_SESSION['profil_success'], $_SESSION['profil_errors']);

        $this->renderLayout('main', 'profil/index', compact(
            'utilisateur', 'csrfToken', 'success', 'errors'
        ));
    }

    public function modifierInfos(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id     = Auth::userId();
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $errors = [];

        if (empty($nom)) {
            $errors[] = 'Le nom est obligatoire.';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        } elseif (!empty($email) && $this->model->emailExiste($email, $id)) {
            $errors[] = 'Cet email est deja utilise par un autre compte.';
        }

        if (!empty($errors)) {
            $_SESSION['profil_errors'] = $errors;
            $this->redirect(BASE_URL . '/profil');
            return;
        }

        $this->model->update($id, [
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
            'role'   => Auth::userRole(),
        ]);

        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['profil_success'] = 'infos';
        $this->redirect(BASE_URL . '/profil');
    }

    public function changerMdp(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id           = Auth::userId();
        $actuel       = $_POST['mdp_actuel']       ?? '';
        $nouveau      = $_POST['mdp_nouveau']       ?? '';
        $confirmation = $_POST['mdp_confirmation']  ?? '';
        $errors       = [];

        // Verifier l'ancien mot de passe via le model
        if (!$this->model->verifierMotDePasse($id, $actuel)) {
            $errors[] = 'Mot de passe actuel incorrect.';
        }
        if (strlen($nouveau) < 6) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
        }
        if ($nouveau !== $confirmation) {
            $errors[] = 'La confirmation ne correspond pas.';
        }
        if (!empty($actuel) && $actuel === $nouveau) {
            $errors[] = "Le nouveau mot de passe doit etre different de l'ancien.";
        }

        if (!empty($errors)) {
            $_SESSION['profil_errors'] = $errors;
            $this->redirect(BASE_URL . '/profil#section-mdp');
            return;
        }

        $this->model->changerMotDePasse($id, $nouveau);
        $_SESSION['profil_success'] = 'mdp';
        $this->redirect(BASE_URL . '/profil');
    }
}