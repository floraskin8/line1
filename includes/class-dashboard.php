<?php
if (!defined('ABSPATH')) exit;

class BlackBeast_Dashboard {

    public function __construct() {
        // Ajouter menu admin
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Hook AJAX pour stats (utilisateurs connectés uniquement)
        add_action('wp_ajax_bba_get_stats', [$this, 'get_stats']);
    }

    /**
     * Ajouter la page admin au menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'BlackBeast Analytics',
            '📈 Analytics',
            'manage_options',
            'bba_dashboard',
            [self::class, 'render_page'],
            'dashicons-chart-area',
            30
        );
    }

    /**
     * Affichage Vue.js dans l’admin
     */
    public static function render_page() {
        ?>
        <div class="wrap">
            <h1>📈 BlackBeast Analytics</h1>

            <div id="bba-dashboard" style="max-width:900px; margin-top: 2rem;">
                <!-- 🔍 Filtres -->
                <div style="margin-bottom:1.5rem; display:flex; gap:1rem; flex-wrap: wrap; align-items: center;">
                    <label>
                        <strong>Période :</strong><br>
                        <select v-model="filters.period" style="min-width:140px;">
                            <option value="">-- Toutes --</option>
                            <option value="today">Aujourd’hui</option>
                            <option value="7days">7 derniers jours</option>
                            <option value="30days">30 derniers jours</option>
                        </select>
                    </label>

                    <label>
                        <strong>Source :</strong><br>
                        <select v-model="filters.source" style="min-width:160px;">
                            <option value="">-- Toutes --</option>
                            <option value="direct">Direct</option>
                            <option value="search">Moteur de recherche</option>
                            <option value="social">Réseaux sociaux</option>
                            <option value="referral">Lien externe</option>
                        </select>
                    </label>

                    <label>
                        <strong>Appareil :</strong><br>
                        <select v-model="filters.device" style="min-width:140px;">
                            <option value="">-- Tous --</option>
                            <option value="desktop">Desktop</option>
                            <option value="mobile">Mobile</option>
                        </select>
                    </label>

                    <button @click="fetchStats" :disabled="loading"
                            style="padding:0.4rem 1rem; background:#4F46E5; color:#fff; border:none; border-radius:4px; cursor:pointer;">
                        {{ loading ? 'Chargement...' : '🔍 Appliquer' }}
                    </button>
                </div>

                <p v-if="errorMsg" style="color:red; margin-bottom:1rem;">{{ errorMsg }}</p>

                <canvas id="visitsChart" style="width:100%; height:400px;"></canvas>

                <!-- 📤 Export CSV -->
                <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" style="margin-top: 2rem;">
                    <?php wp_nonce_field('bba_export_action'); ?>
                    <input type="hidden" name="action" value="bba_export_csv">
                    <button type="submit" class="button button-primary">📤 Exporter les données en CSV</button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Requête AJAX pour stats (JSON)
     */
    public function get_stats() {
    check_ajax_referer('bba_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Non autorisé'], 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'blackbeast_visits';

    $results = $wpdb->get_results("
        SELECT DATE(timestamp) as date, COUNT(*) as views
        FROM $table
        GROUP BY DATE(timestamp)
        ORDER BY DATE(timestamp)
    ");

    $data = array_map(function ($row) {
        return [
            'date' => $row->date,
            'views' => (int) $row->views
        ];
    }, $results);

    wp_send_json($data);
}

}
