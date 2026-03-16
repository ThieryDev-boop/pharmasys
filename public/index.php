<?php
// public/index.php

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('BASE_URL',    '/pharmasys/public');

require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';

Auth::startSecureSession();

$router = new Router();

// ── Authentification ─────────────────────────────────────────
$router->add('GET',  '/auth/login',  'AuthController', 'loginForm');
$router->add('POST', '/auth/login',  'AuthController', 'login');
$router->add('GET',  '/auth/logout', 'AuthController', 'logout');

// ── Dashboard ────────────────────────────────────────────────
$router->add('GET', '/',          'DashboardController', 'index');
$router->add('GET', '/dashboard', 'DashboardController', 'index');

// ── Medicaments ──────────────────────────────────────────────
$router->add('GET',  '/medicaments',        'MedicamentController', 'index');
$router->add('GET',  '/medicaments/create', 'MedicamentController', 'createForm');
$router->add('POST', '/medicaments/create', 'MedicamentController', 'create');
$router->add('GET',  '/medicaments/edit',   'MedicamentController', 'editForm');
$router->add('POST', '/medicaments/edit',   'MedicamentController', 'edit');
$router->add('POST', '/medicaments/delete', 'MedicamentController', 'delete');

// ── Lots ─────────────────────────────────────────────────────
$router->add('GET',  '/lots',        'LotController', 'index');
$router->add('GET',  '/lots/create', 'LotController', 'createForm');
$router->add('POST', '/lots/create', 'LotController', 'create');

// ── Ventes ───────────────────────────────────────────────────
$router->add('GET',  '/ventes',                      'VenteController', 'index');
$router->add('GET',  '/ventes/create',               'VenteController', 'createForm');
$router->add('POST', '/ventes/init',                 'VenteController', 'init');
$router->add('GET',  '/ventes/rechercher-medicament','VenteController', 'rechercherMedicament');
$router->add('POST', '/ventes/ajouter-ligne',        'VenteController', 'ajouterLigne');
$router->add('POST', '/ventes/supprimer-ligne',      'VenteController', 'supprimerLigne');
$router->add('POST', '/ventes/valider',              'VenteController', 'valider');
$router->add('GET',  '/ventes/recu',                 'VenteController', 'imprimerRecu');

// ── Clients ──────────────────────────────────────────────────
$router->add('GET',  '/clients',             'ClientController', 'index');
$router->add('POST', '/clients/create',      'ClientController', 'create');
$router->add('GET',  '/clients/edit',        'ClientController', 'editForm');
$router->add('POST', '/clients/edit',        'ClientController', 'edit');
$router->add('GET',  '/clients/historique',  'ClientController', 'historique');
$router->add('POST', '/clients/desactiver',  'ClientController', 'desactiver');
$router->add('GET',  '/clients/rechercher',  'ClientController', 'rechercher');

// ── Fournisseurs ─────────────────────────────────────────────
$router->add('GET',  '/fournisseurs',             'FournisseurController', 'index');
$router->add('POST', '/fournisseurs/create',      'FournisseurController', 'create');
$router->add('POST', '/fournisseurs/edit',        'FournisseurController', 'edit');
$router->add('GET',  '/fournisseurs/detail',      'FournisseurController', 'detail');
$router->add('POST', '/fournisseurs/desactiver',  'FournisseurController', 'desactiver');

// ── Commandes ────────────────────────────────────────────────
$router->add('GET',  '/commandes',                'CommandeController', 'index');
$router->add('GET',  '/commandes/create',         'CommandeController', 'create');
$router->add('POST', '/commandes/init',           'CommandeController', 'init');
$router->add('POST', '/commandes/ajouter-ligne',  'CommandeController', 'ajouterLigne');
$router->add('POST', '/commandes/supprimer-ligne','CommandeController', 'supprimerLigne');
$router->add('GET',  '/commandes/detail',         'CommandeController', 'detail');
$router->add('POST', '/commandes/envoyer',        'CommandeController', 'envoyer');
$router->add('POST', '/commandes/recevoir',       'CommandeController', 'recevoir');
$router->add('POST', '/commandes/annuler',        'CommandeController', 'annuler');

// ── Rapports ─────────────────────────────────────────────────
$router->add('GET', '/rapports',             'RapportController', 'index');
$router->add('GET', '/rapports/ventes-pdf',  'RapportController', 'ventesPdf');
$router->add('GET', '/rapports/stock-excel', 'RapportController', 'stockExcel');
$router->add('GET', '/rapports/alertes-pdf', 'RapportController', 'alertesPdf');
$router->add('GET', '/rapports',             'RapportController', 'index');
$router->add('GET', '/rapports/ventes',      'RapportController', 'ventes');
$router->add('GET', '/rapports/stock',       'RapportController', 'stock');
$router->add('GET', '/rapports/fournisseurs','RapportController', 'fournisseurs');
$router->add('GET', '/rapports/clients',     'RapportController', 'clients');

// ── Administration ───────────────────────────────────────────
// Utilisateurs
$router->add('GET',  '/admin/utilisateurs',         'AdminController', 'utilisateurs');
$router->add('POST', '/admin/creer-utilisateur',    'AdminController', 'creerUtilisateur');
$router->add('POST', '/admin/modifier-utilisateur', 'AdminController', 'modifierUtilisateur');
$router->add('POST', '/admin/toggle-actif',         'AdminController', 'toggleActif');
$router->add('POST', '/admin/reinit-mdp',           'AdminController', 'reinitMdp');

// Sauvegardes
$router->add('GET',  '/admin/sauvegardes',             'SauvegardeController', 'index');
$router->add('POST', '/admin/sauvegardes/creer',       'SauvegardeController', 'creer');
$router->add('GET',  '/admin/sauvegardes/telecharger', 'SauvegardeController', 'telecharger');
$router->add('POST', '/admin/sauvegardes/supprimer',   'SauvegardeController', 'supprimer');

// Profil
$router->add('GET',  '/profil',                'ProfilController', 'index');
$router->add('POST', '/profil/modifier-infos', 'ProfilController', 'modifierInfos');
$router->add('POST', '/profil/changer-mdp',    'ProfilController', 'changerMdp');

// ── Dispatch ─────────────────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/pharmasys/public', '', $uri);
if (empty($uri)) $uri = '/';

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);