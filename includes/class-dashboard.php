<?php
if (!defined('ABSPATH')) exit;

class BlackBeast_Dashboard {

    public function __construct() {
        // Enregistre les hooks AJAX (uniquement pour utilisateurs connectés)
        add_action('wp_ajax_bba_get_stats', [$this, 'get_stats']);
    }

    // Affichage de la page admin
  
public static function render_page() {
    ?>
    <div class="wrap">
        <h1>📈 BlackBeast Analytics</h1>

        <div id="bba-dashboard" style="max-width:900px;">
            <div style="margin-bottom:1rem; display:flex; gap:1rem; flex-wrap: wrap;">

                <label>
                    Période :
                    <select v-model="filters.period">
                        <option value="">-- Toutes --</option>
                        <option value="today">Aujourd’hui</option>
                        <option value="7days">7 derniers jours</option>
                        <option value="30days">30 derniers jours</option>
                    </select>
                </label>

                <label>
                    Source :
                    <select v-model="filters.source">
                        <option value="">-- Toutes --</option>
                        <option value="direct">Direct</option>
                        <option value="search">Moteur de recherche</option>
                        <option value="social">Réseaux sociaux</option>
                        <option value="referral">Lien externe</option>
                    </select>
                </label>

                <label>
                    Appareil :
                    <select v-model="filters.device">
                        <option value="">-- Tous --</option>
                        <option value="desktop">Desktop</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </label>

                <button @click="fetchStats" :disabled="loading" style="padding:0.4rem 1rem; cursor:pointer;">
                    {{ loading ? 'Chargement...' : '🔍 Appliquer' }}
                </button>

            </div>

            <p v-if="errorMsg" style="color:red; margin-bottom:1rem;">{{ errorMsg }}</p>

            <canvas id="visitsChart" style="max-width: 100%; height: 400px;"></canvas>
        </div>
    </div>
    <?php
}

    // Handler AJAX sécurisé
    public function get_stats() {
        check_ajax_referer('bba_nonce', 'nonce');

        // Exemple statique, à remplacer par ta logique de requête base de données
        $data = [
            ['date' => '2025-07-01', 'views' => 10],
            ['date' => '2025-07-02', 'views' => 15],
            ['date' => '2025-07-03', 'views' => 7],
            ['date' => '2025-07-04', 'views' => 20],
            ['date' => '2025-07-05', 'views' => 13],
        ];

        wp_send_json($data);
    }
}
