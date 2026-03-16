<?php
// app/controllers/AdminController.php

require_once APP_PATH . '/models/UtilisateurModel.php';

class AdminController extends Controller {

    private $model;

    public function __construct() {
        Auth::requireAuth();
        Auth::requireRole('administrateur'); // Admin seulement
        $this->model = new UtilisateurModel();
    }

    // Liste des utilisateurs
    public function utilisateurs(): void {
        $page    = max(1, (int)($_GET['page']   ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $perPage = 20;

        $utilisateurs = $this->model->findAll($page, $perPage, $search);
        $total        = $this->model->countAll($search);
        $totalPages   = max(1, ceil($total / $perPage));
        $stats        = $this->model->getStats();
        $csrfToken    = Auth::generateCsrfToken();

        // Erreurs formulaire depuis session
        $errors  = $_SESSION['form_errors'] ?? [];
        $formOld = $_SESSION['form_old']    ?? [];
        unset($_SESSION['form_errors'], $_SESSION['form_old']);

        $this->renderLayout('main', 'admin/utilisateurs', compact(
            'utilisateurs', 'total', 'page', 'totalPages',
            'search', 'stats', 'csrfToken', 'errors', 'formOld'
        ));
    }

    // Creer un utilisateur (POST depuis modale)
    public function creerUtilisateur(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $errors = $this->valider($_POST);
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            $this->redirect(BASE_URL . '/admin/utilisateurs?erreur=1');
            return;
        }

        $this->model->create([
            'nom'          => trim($_POST['nom']),
            'prenom'       => trim($_POST['prenom'] ?? ''),
            'login'        => trim($_POST['login'] ?? ''), // vide = auto-genere
            'email'        => trim($_POST['email'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe'],
            'role'         => $_POST['role'],
        ]);

        $this->redirect(BASE_URL . '/admin/utilisateurs?success=cree');
    }

    // Modifier un utilisateur (POST depuis modale)
    public function modifierUtilisateur(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id     = (int)($_POST['id_utilisateur'] ?? 0);
        $errors = $this->validerEdition($_POST, $id);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            $this->redirect(BASE_URL . '/admin/utilisateurs?erreur=1');
            return;
        }

        $this->model->update($id, [
            'nom'    => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'email'  => trim($_POST['email']),
            'role'   => $_POST['role'],
        ]);

        // Changer mdp si fourni
        if (!empty($_POST['mot_de_passe'])) {
            $this->model->changerMotDePasse($id, $_POST['mot_de_passe']);
        }

        $this->redirect(BASE_URL . '/admin/utilisateurs?success=modifie');
    }

    // Activer / desactiver
    public function toggleActif(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id = (int)($_POST['id_utilisateur'] ?? 0);

        // Empecher de se desactiver soi-meme
        if ($id === Auth::userId()) {
            $this->redirect(BASE_URL . '/admin/utilisateurs?erreur=selfdeactivate');
            return;
        }

        $this->model->toggleActif($id);
        $this->redirect(BASE_URL . '/admin/utilisateurs?success=modifie');
    }

    // Reinitialiser le mot de passe (genere un mdp temporaire)
    public function reinitMdp(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id      = (int)($_POST['id_utilisateur'] ?? 0);
        $nouveau = 'Pharma' . rand(1000, 9999) . '!';

        $this->model->changerMotDePasse($id, $nouveau);

        // Stocker le mdp temporaire en session pour l'afficher
        $_SESSION['mdp_temp']      = $nouveau;
        $_SESSION['mdp_temp_user'] = $id;

        $this->redirect(BASE_URL . '/admin/utilisateurs?success=reinit');
    }

    // ── Validation ───────────────────────────────────────────

    private function valider(array $post): array {
        $errors = [];
        if (empty(trim($post['nom'] ?? ''))) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        $login = trim($post['login'] ?? '');
        if (!empty($login)) {
            if (!preg_match('/^[a-z0-9._-]{3,30}$/', $login)) {
                $errors['login'] = 'Login invalide (3-30 caracteres, lettres minuscules, chiffres, . _ - uniquement).';
            } elseif ($this->model->loginExiste($login)) {
                $errors['login'] = 'Ce login est deja utilise.';
            }
        }
        $email = trim($post['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif (!empty($email) && $this->model->emailExiste($email)) {
            $errors['email'] = 'Cet email est deja utilise.';
        }
        if (empty($post['mot_de_passe'])) {
            $errors['mot_de_passe'] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($post['mot_de_passe']) < 6) {
            $errors['mot_de_passe'] = 'Minimum 6 caracteres.';
        }
        if (!in_array($post['role'] ?? '', ['administrateur', 'pharmacien', 'caissier'])) {
            $errors['role'] = 'Role invalide.';
        }
        return $errors;
    }

    private function validerEdition(array $post, int $id): array {
        $errors = [];
        if (empty(trim($post['nom'] ?? ''))) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (empty(trim($post['email'] ?? '')) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($this->model->emailExiste(trim($post['email']), $id)) {
            $errors['email'] = 'Cet email est deja utilise.';
        }
        if (!empty($post['mot_de_passe']) && strlen($post['mot_de_passe']) < 6) {
            $errors['mot_de_passe'] = 'Minimum 6 caracteres.';
        }
        if (!in_array($post['role'] ?? '', ['administrateur', 'pharmacien', 'caissier'])) {
            $errors['role'] = 'Role invalide.';
        }
        return $errors;
    }
}