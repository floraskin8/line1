<?php
/*
Plugin Name: BlackBeast Analytics
Description: Analytics auto-hébergé RGPD avec tracking local.
Version: 1.0.0
Author: Black_beast
*/

if (!defined('ABSPATH')) exit;

// 📁 Définir les chemins
define('BBA_PATH', plugin_dir_path(__FILE__));
define('BBA_URL', plugin_dir_url(__FILE__));

// 📦 Inclure les classes principales
require_once BBA_PATH . 'includes/class-dashboard.php';
require_once BBA_PATH . 'includes/class-export.php';
require_once BBA_PATH . 'includes/class-tracker.php';

// ✅ Hook d'activation (création des tables)
register_activation_hook(__FILE__, 'bba_install');
function bba_install() {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    (new BlackBeast_Tracker())->create_table();
}

// ❌ Hook de désactivation/désinstallation (facultatif)
register_deactivation_hook(__FILE__, 'bba_uninstall');
function bba_uninstall() {
    global $wpdb;
    $table = $wpdb->prefix . 'blackbeast_visits';
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// ⚙️ Ajout du menu admin
add_action('admin_menu', function () {
    add_menu_page(
        'BlackBeast Analytics',
        '📊 BlackBeast',
        'manage_options',
        'blackbeast-analytics',
        [BlackBeast_Dashboard::class, 'render_page'],
        'dashicons-chart-area'
    );
});

// 📊 Scripts et styles (admin uniquement)
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_blackbeast-analytics') return;

    // ✅ CSS pour le dashboard admin
    wp_enqueue_style('bba-admin-style', BBA_URL . 'assets/css/admin-style.css', [], '1.0');

    // ✅ Librairies JS nécessaires
    wp_enqueue_script('vue', BBA_URL . 'assets/libs/vue.global.prod.js', [], '3.0.0', true);
    wp_enqueue_script('chartjs', BBA_URL . 'assets/libs/chart.umd.js', [], '4.4.0', true);

    // ✅ Script admin (dashboard Vue.js)
    wp_enqueue_script('bba-admin', BBA_URL . 'assets/js/admin-dashboard.js', ['vue', 'chartjs'], '1.0', true);
    wp_localize_script('bba-admin', 'bba_ajax', [ // ✅ nom de script corrigé ici
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bba_nonce')
    ]);

    // ✅ Script public (tracking JS injecté même sur le dashboard)
    wp_enqueue_script('bba-tracker', BBA_URL . 'assets/js/tracking.js', [], '1.0', true);
    wp_localize_script('bba-tracker', 'bba_tracker', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bba_nonce')
    ]);
});

// ✅ Injection du script de tracking sur le frontend
add_action('wp_enqueue_scripts', function () {
    if (is_admin() || is_user_logged_in()) return;

    wp_enqueue_script(
        'bba-tracking',
        BBA_URL . 'assets/js/tracking.js',
        [],
        '1.0',
        true
    );

    wp_localize_script('bba-tracking', 'bba_tracker', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bba_nonce')
    ]);
});

// 🚀 Initialisation des classes
add_action('plugins_loaded', function () {
    new BlackBeast_Tracker();
    new BlackBeast_Dashboard();
    new BlackBeast_Export();
});
