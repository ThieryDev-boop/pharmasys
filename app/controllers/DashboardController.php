<?php
// app/controllers/DashboardController.php

class DashboardController extends Controller {

    public function __construct() {
        Auth::requireAuth();
    }

    public function index(): void {
        // Pour l'instant on affiche un dashboard simple
        // On ajoutera les stats plus tard
        $stats = [
            'ventes_jour' => 0,
            'ca_jour'     => 0,
            'ca_mois'     => 0,
        ];
        $alertesStock      = [];
        $alertesPeremption = [];

        // Charger les stats si la base est prete
        try {
            require_once APP_PATH . '/models/VenteModel.php';
            $venteModel = new VenteModel();
            $stats = $venteModel->getStatsDashboard();

            require_once APP_PATH . '/models/MedicamentModel.php';
            $medModel = new MedicamentModel();
            $alertesStock = $medModel->getAlertesStock();

            require_once APP_PATH . '/models/LotModel.php';
            $lotModel = new LotModel();
            $alertesPeremption = $lotModel->getAlertesPeremption(90);

        } catch (Exception $e) {
            // Si les tables n'existent pas encore, on affiche quand meme le dashboard
        }

        $this->renderLayout('main', 'dashboard/index', compact(
            'stats', 'alertesStock', 'alertesPeremption'
        ));
    }
}
