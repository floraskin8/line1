<?php

class BlackBeast_Export
{
    public function __construct()
    {
        // Ajouter le handler AJAX pour export
        add_action('wp_ajax_bba_export_csv', [$this, 'export_csv']);
    }

    public function export_csv()
    {
        // Vérifie les permissions
        if (!current_user_can('manage_options')) {
            wp_die('Accès refusé');
        }

        // Vérifie le nonce de sécurité
        check_admin_referer('bba_export_action');

        global $wpdb;
        $table = $wpdb->prefix . 'blackbeast_visits';

        // Requête SQL
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC", ARRAY_A);

        if (empty($results)) {
            wp_die('Aucune donnée à exporter.');
        }

        // Préparer le fichier CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=blackbeast_visits_export.csv');

        $output = fopen('php://output', 'w');

        // En-têtes
        fputcsv($output, array_keys($results[0]));

        // Lignes
        foreach ($results as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
