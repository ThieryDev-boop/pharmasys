<?php
// app/controllers/AuthController.php

require_once APP_PATH . '/models/UtilisateurModel.php';

class AuthController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = new UtilisateurModel();
    }

    // Afficher le formulaire de connexion
    public function loginForm(): void {
        // Si deja connecte, rediriger vers le dashboard
        if (Auth::isAuthenticated()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        $csrfToken = Auth::generateCsrfToken();
        $this->renderLayout('auth', 'auth/login', [
            'csrfToken' => $csrfToken,
            'error'     => null
        ]);
    }

    // Traiter le formulaire de connexion
    public function login(): void {
        $this->requirePost();

        // Verification CSRF
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Token CSRF invalide. Rechargez la page.');
        }

        $login    = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation basique
        if (empty($login) || empty($password)) {
            $this->renderLayout('auth', 'auth/login', [
                'csrfToken' => Auth::generateCsrfToken(),
                'error'     => 'Veuillez remplir tous les champs.'
            ]);
            return;
        }

        $user = $this->userModel->findByLogin($login);

        // Verifier si le compte est bloque
        if ($user && !empty($user['bloque_jusqu_a'])) {
            if (strtotime($user['bloque_jusqu_a']) > time()) {
                $this->renderLayout('auth', 'auth/login', [
                    'csrfToken' => Auth::generateCsrfToken(),
                    'error'     => 'Compte bloque temporairement. Reessayez dans quelques minutes.'
                ]);
                return;
            }
        }

        // Verifier les identifiants
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->userModel->resetTentatives($user['id_utilisateur']);
            Auth::login($user);
            $this->redirect(BASE_URL . '/dashboard');
        } else {
            if ($user) {
                $this->userModel->incrementTentatives($user['id_utilisateur']);
            }
            $this->renderLayout('auth', 'auth/login', [
                'csrfToken' => Auth::generateCsrfToken(),
                'error'     => 'Identifiant ou mot de passe incorrect.'
            ]);
        }
    }

    // Deconnexion
    public function logout(): void {
        Auth::logout();
        $this->redirect(BASE_URL . '/auth/login');
    }
}
