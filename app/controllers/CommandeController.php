<?php
// app/controllers/CommandeController.php

require_once APP_PATH . '/models/CommandeModel.php';
require_once APP_PATH . '/models/FournisseurModel.php';

class CommandeController extends Controller {

    private CommandeModel $model;

    public function __construct() {
        Auth::requireAuth();
        $this->model = new CommandeModel();
    }

    // ── LISTE ────────────────────────────────────────────────

    public function index(): void {
        $statut = $_GET['statut'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $filtres    = array_filter(['statut' => $statut, 'search' => $search]);
        $commandes  = $this->model->findAll($filtres);
        $compteurs  = $this->model->compterParStatut();
        $csrfToken  = Auth::generateCsrfToken();

        $this->renderLayout('main', 'commandes/index', compact(
            'commandes', 'compteurs', 'statut', 'search', 'csrfToken'
        ));
    }

    // ── FORMULAIRE CRÉATION ───────────────────────────────────

    public function create(): void {
        $config = require CONFIG_PATH . '/database.php';
        $dsn    = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo    = new PDO($dsn, $config['username'], $config['password'], $config['options']);

        $stmt = $pdo->query(
            "SELECT id_fournisseur, raison_sociale FROM fournisseurs
             WHERE actif = 1 ORDER BY raison_sociale ASC"
        );
        $fournisseurs   = $stmt->fetchAll();
        $idFournisseur  = (int)($_GET['fournisseur'] ?? 0);
        $csrfToken      = Auth::generateCsrfToken();

        $this->renderLayout('main', 'commandes/create', compact(
            'fournisseurs', 'idFournisseur', 'csrfToken'
        ));
    }

    // ── AJAX : initialiser commande ───────────────────────────

    public function init(): void {
        ob_start();
        try {
            if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token invalide.');
            }

            $idFournisseur = (int)($_POST['id_fournisseur'] ?? 0);
            $note          = trim($_POST['note'] ?? '');
            $dateLivraison = trim($_POST['date_livraison'] ?? '');

            if (!$idFournisseur) throw new Exception('Fournisseur requis.');

            // Vérifier que le fournisseur existe
            $config = require CONFIG_PATH . '/database.php';
            $dsn    = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $pdo    = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            $stmt   = $pdo->prepare("SELECT raison_sociale FROM fournisseurs WHERE id_fournisseur = :id AND actif = 1");
            $stmt->execute([':id' => $idFournisseur]);
            $fournisseur = $stmt->fetch();
            if (!$fournisseur) throw new Exception('Fournisseur introuvable.');

            $idCommande = $this->model->creer(
                $idFournisseur,
                Auth::userId(),
                $note,
                $dateLivraison
            );

            $commande = $this->model->findById($idCommande);

            ob_end_clean();
            $this->json(['success' => true,
                'id_commande' => $idCommande,
                'numero'      => $commande['numero_commande'],
                'fournisseur' => $fournisseur['raison_sociale'],
            ]);
        } catch (Exception $e) {
            ob_end_clean();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── AJAX : ajouter ligne ──────────────────────────────────

    public function ajouterLigne(): void {
        ob_start();
        try {
            if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token invalide.');
            }

            $idCommande   = (int)($_POST['id_commande']  ?? 0);
            $idMedicament = (int)($_POST['id_medicament'] ?? 0);
            $qte          = (int)($_POST['quantite']     ?? 0);
            $prix         = (float)($_POST['prix_unitaire'] ?? 0);

            if (!$idCommande || !$idMedicament) throw new Exception('Données manquantes.');
            if ($qte <= 0)  throw new Exception('Quantité invalide.');
            if ($prix < 0)  throw new Exception('Prix invalide.');

            $this->model->ajouterLigne($idCommande, $idMedicament, $qte, $prix);

            $lignes = $this->model->getLignes($idCommande);
            $cmd    = $this->model->findById($idCommande);

            ob_end_clean();
            $this->json(['success' => true,
                'lignes' => $lignes,
                'total'  => $cmd['montant_total'],
            ]);
        } catch (Exception $e) {
            ob_end_clean();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── AJAX : supprimer ligne ────────────────────────────────

    public function supprimerLigne(): void {
        ob_start();
        try {
            if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token invalide.');
            }

            $idLigne    = (int)($_POST['id_ligne']    ?? 0);
            $idCommande = (int)($_POST['id_commande'] ?? 0);
            if (!$idLigne || !$idCommande) throw new Exception('Données manquantes.');

            $this->model->supprimerLigne($idLigne, $idCommande);

            $lignes = $this->model->getLignes($idCommande);
            $cmd    = $this->model->findById($idCommande);

            ob_end_clean();
            $this->json(['success' => true,
                'lignes' => $lignes,
                'total'  => $cmd['montant_total'],
            ]);
        } catch (Exception $e) {
            ob_end_clean();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── DÉTAIL ────────────────────────────────────────────────

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $commande = $this->model->findById($id);
        if (!$commande) {
            $this->redirect(BASE_URL . '/commandes');
            return;
        }

        $lignes    = $this->model->getLignes($id);
        $csrfToken = Auth::generateCsrfToken();

        $this->renderLayout('main', 'commandes/detail', compact(
            'commande', 'lignes', 'csrfToken'
        ));
    }

    // ── ENVOYER ───────────────────────────────────────────────

    public function envoyer(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id     = (int)($_POST['id_commande'] ?? 0);
        $lignes = $this->model->getLignes($id);

        if (empty($lignes)) {
            $_SESSION['flash_error'] = 'Impossible d\'envoyer une commande vide.';
            $this->redirect(BASE_URL . '/commandes/detail?id=' . $id);
            return;
        }

        $this->model->envoyer($id);
        $this->redirect(BASE_URL . '/commandes/detail?id=' . $id . '&success=envoye');
    }

    // ── RÉCEPTION ────────────────────────────────────────────

    public function recevoir(): void {
    $this->requirePost();
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

    $idCommande = (int)($_POST['id_commande'] ?? 0);
    $receptions = $_POST['reception'] ?? [];

    if (empty($receptions)) {
        $this->redirect(BASE_URL . '/commandes/detail?id=' . $idCommande);
        return;
    }

    try {
        $this->model->enregistrerReception($idCommande, $receptions);
        $this->redirect(BASE_URL . '/commandes/detail?id=' . $idCommande . '&success=recu');
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Erreur lors de la réception : ' . $e->getMessage();
        $this->redirect(BASE_URL . '/commandes/detail?id=' . $idCommande);
    }
}

    // ── ANNULER ───────────────────────────────────────────────

    public function annuler(): void {
        $this->requirePost();
        if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) die('CSRF invalide.');

        $id = (int)($_POST['id_commande'] ?? 0);
        $this->model->annuler($id);
        $this->redirect(BASE_URL . '/commandes?success=annule');
    }

}
