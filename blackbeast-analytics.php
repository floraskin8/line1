<?php
/*
Plugin Name: BlackBeast Analytics
Description: Analytics auto-hébergé RGPD avec tracking local.
Version: 1.0.0
Author: Black_beast
*/

if (!defined('ABSPATH')) exit;

define('BBA_PATH', plugin_dir_path(__FILE__));
define('BBA_URL', plugin_dir_url(__FILE__));

// Charger le tracker et le dashboard
require_once BBA_PATH . 'includes/class-dashboard.php';

// Activation / désactivation
register_activation_hook(__FILE__, 'bba_install');
register_deactivation_hook(__FILE__, 'bba_uninstall');

// Enqueue scripts et styles admin
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_blackbeast-analytics') return;

    wp_enqueue_style('bba-admin-style', BBA_URL . 'assets/css/admin-style.css', [], '1.0');

    wp_enqueue_script('vue', 'https://unpkg.com/vue@3', [], null, true);
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_script('bba-admin', BBA_URL . 'assets/js/admin-dashboard.js', ['vue', 'chartjs'], '1.0', true);

    wp_localize_script('bba-admin', 'bba_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bba_nonce')
    ]);
});

// Ajouter la page admin
add_action('admin_menu', function() {
    add_menu_page(
        'BlackBeast Analytics',
        'BlackBeast',
        'manage_options',
        'blackbeast-analytics',
        [BlackBeast_Dashboard::class, 'render_page'],
        'dashicons-chart-area'
    );
});

// Initialisation du dashboard
add_action('plugins_loaded', function() {
    new BlackBeast_Dashboard();
});
