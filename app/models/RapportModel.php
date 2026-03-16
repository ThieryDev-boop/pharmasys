<?php
// app/models/RapportModel.php

class RapportModel extends Model {

    // ── VENTES ───────────────────────────────────────────────

    // Stats ventes sur une periode
    public function getStatsVentes(string $dateDebut, string $dateFin): array {
        return $this->queryOne(
            "SELECT
                COUNT(*) AS nb_ventes,
                COALESCE(SUM(montant_total_ttc), 0) AS ca_total,
                COALESCE(AVG(montant_total_ttc), 0) AS panier_moyen,
                COALESCE(MAX(montant_total_ttc), 0) AS vente_max
             FROM ventes
             WHERE statut = 'validee'
               AND DATE(date_vente) BETWEEN :d1 AND :d2",
            [':d1' => $dateDebut, ':d2' => $dateFin]
        ) ?: [];
    }

    // Detail des ventes sur une periode
    public function getVentesPeriode(string $dateDebut, string $dateFin): array {
        return $this->query(
            "SELECT v.*,
                    CONCAT(u.nom,' ',u.prenom) AS caissier_nom,
                    COALESCE(CONCAT(c.nom,' ',c.prenom), 'Passage') AS client_nom
             FROM ventes v
             JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
             LEFT JOIN clients c ON v.id_client = c.id_client
             WHERE v.statut = 'validee'
               AND DATE(v.date_vente) BETWEEN :d1 AND :d2
             ORDER BY v.date_vente DESC",
            [':d1' => $dateDebut, ':d2' => $dateFin]
        );
    }

    // Top medicaments vendus
    public function getTopMedicaments(string $dateDebut, string $dateFin, int $limite = 10): array {
        return $this->query(
            "SELECT m.nom_commercial, m.dci,
                    SUM(lv.quantite) AS total_vendu,
                    SUM(lv.montant_ligne) AS ca_genere
             FROM lignes_vente lv
             JOIN medicaments m ON lv.id_medicament = m.id_medicament
             JOIN ventes v ON lv.id_vente = v.id_vente
             WHERE v.statut = 'validee'
               AND DATE(v.date_vente) BETWEEN :d1 AND :d2
             GROUP BY m.id_medicament
             ORDER BY total_vendu DESC
             LIMIT " . (int)$limite,
            [':d1' => $dateDebut, ':d2' => $dateFin]
        );
    }

    // Ventes par jour (pour graphique)
    public function getVentesParJour(string $dateDebut, string $dateFin): array {
        return $this->query(
            "SELECT DATE(date_vente) AS jour,
                    COUNT(*) AS nb_ventes,
                    SUM(montant_total_ttc) AS ca_jour
             FROM ventes
             WHERE statut = 'validee'
               AND DATE(date_vente) BETWEEN :d1 AND :d2
             GROUP BY DATE(date_vente)
             ORDER BY jour ASC",
            [':d1' => $dateDebut, ':d2' => $dateFin]
        );
    }

    // Ventes par caissier
    public function getVentesParCaissier(string $dateDebut, string $dateFin): array {
        return $this->query(
            "SELECT CONCAT(u.nom,' ',u.prenom) AS caissier,
                    COUNT(*) AS nb_ventes,
                    SUM(v.montant_total_ttc) AS ca_total
             FROM ventes v
             JOIN utilisateurs u ON v.id_utilisateur = u.id_utilisateur
             WHERE v.statut = 'validee'
               AND DATE(v.date_vente) BETWEEN :d1 AND :d2
             GROUP BY v.id_utilisateur
             ORDER BY ca_total DESC",
            [':d1' => $dateDebut, ':d2' => $dateFin]
        );
    }

    // ── STOCK ────────────────────────────────────────────────

    // Etat global du stock
    public function getEtatStock(): array {
        return $this->query(
            "SELECT m.*,
                    c.nom,
                    COALESCE(SUM(l.quantite_restante), 0) AS stock_total,
                    m.seuil_minimum,
                    MIN(CASE WHEN l.quantite_restante > 0
                             THEN l.date_peremption END) AS prochaine_peremption
             FROM medicaments m
             LEFT JOIN categories c ON m.id_categorie = c.id_categorie
             LEFT JOIN lots l ON m.id_medicament = l.id_medicament
             WHERE m.statut = 'actif'
             GROUP BY m.id_medicament
             ORDER BY stock_total ASC"
        );
    }

    // Medicaments en rupture ou alerte
    public function getAlertesStock(): array {
        return $this->query(
            "SELECT m.nom_commercial, m.dci, m.seuil_minimum,
                    COALESCE(SUM(l.quantite_restante), 0) AS stock_total,
                    CASE
                        WHEN COALESCE(SUM(l.quantite_restante), 0) = 0 THEN 'rupture'
                        WHEN COALESCE(SUM(l.quantite_restante), 0) <= m.seuil_minimum THEN 'alerte'
                    END AS type_alerte
             FROM medicaments m
             LEFT JOIN lots l ON m.id_medicament = l.id_medicament
             WHERE m.statut = 'actif'
             GROUP BY m.id_medicament
             HAVING stock_total <= m.seuil_minimum
             ORDER BY stock_total ASC"
        );
    }

    // Lots proches de peremption
    public function getLotsPeremption(int $jours = 90): array {
        return $this->query(
            "SELECT l.*, m.nom_commercial, m.dci,
                    DATEDIFF(l.date_peremption, CURDATE()) AS jours_restants
             FROM lots l
             JOIN medicaments m ON l.id_medicament = m.id_medicament
             WHERE l.quantite_restante > 0
               AND l.date_peremption <= DATE_ADD(CURDATE(), INTERVAL :j DAY)
             ORDER BY l.date_peremption ASC",
            [':j' => $jours]
        );
    }

    // Valeur totale du stock
    public function getValeurStock() {
        return $this->queryOne(
            "SELECT
                COALESCE(SUM(l.quantite_restante * l.prix_achat), 0) AS valeur_achat,
                COALESCE(SUM(l.quantite_restante * m.prix_vente), 0) AS valeur_vente,
                COUNT(DISTINCT m.id_medicament) AS nb_references,
                SUM(l.quantite_restante) AS total_unites
             FROM lots l
             JOIN medicaments m ON l.id_medicament = m.id_medicament
             WHERE l.quantite_restante > 0"
        );
    }

    // ── FOURNISSEURS / COMMANDES ─────────────────────────────

    // Stats par fournisseur
    public function getStatsFournisseurs(): array {
        return $this->query(
            "SELECT f.raison_sociale, f.telephone, f.email,
                    COUNT(c.id_commande) AS nb_commandes,
                    COALESCE(SUM(CASE WHEN c.statut='recue' THEN c.montant_total END), 0) AS total_achats,
                    MAX(c.date_commande) AS derniere_commande
             FROM fournisseurs f
             LEFT JOIN commandes c ON f.id_fournisseur = c.id_fournisseur
             WHERE f.actif = 1
             GROUP BY f.id_fournisseur
             ORDER BY total_achats DESC"
        );
    }

    // ── CLIENTS ──────────────────────────────────────────────

    // Top clients
    public function getTopClients(int $limite = 10): array {
        return $this->query(
            "SELECT CONCAT(c.nom,' ',COALESCE(c.prenom,'')) AS client_nom,
                    c.telephone,
                    COUNT(v.id_vente) AS nb_achats,
                    COALESCE(SUM(v.montant_total_ttc), 0) AS total_depense,
                    COALESCE(AVG(v.montant_total_ttc), 0) AS panier_moyen,
                    MAX(v.date_vente) AS dernier_achat
             FROM clients c
             JOIN ventes v ON c.id_client = v.id_client
             WHERE v.statut = 'validee'
             GROUP BY c.id_client
             ORDER BY total_depense DESC
             LIMIT " . (int)$limite
        );
    }
}
