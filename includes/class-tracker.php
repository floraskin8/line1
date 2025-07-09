<?php
if (!defined('ABSPATH')) exit;

class BlackBeast_Tracker {

    private $table_visits;

    public function __construct() {
        global $wpdb;
        $this->table_visits = $wpdb->prefix . 'blackbeast_visits';

        // Crée la table si elle n'existe pas
        register_activation_hook(BBA_PATH . 'blackbeast-analytics.php', [$this, 'create_table']);

        // Traque chaque page vue côté frontend (pas admin)
       add_action('wp_ajax_nopriv_bba_track_visit', [$this, 'handle_visit']);
add_action('wp_ajax_bba_track_visit', [$this, 'handle_visit']); // (optionnel, pour utilisateurs connectés)

        // Cleanup session durée
        add_action('shutdown', [$this, 'track_duration']);
    }

    /**
     * Création de la table SQL pour stocker les visites
     */
 public function create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'blackbeast_visits';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(64) NOT NULL,
        url TEXT NOT NULL,
        referrer TEXT,
        source VARCHAR(20),
        user_agent TEXT,
        ip VARCHAR(45),
        visit_time DATETIME NOT NULL,
        duration INT DEFAULT 0
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}


    public function handle_visit() {
        check_ajax_referer('bba_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'blackbeast_visits';

        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $url        = esc_url_raw($_POST['url'] ?? '');
        $referrer   = esc_url_raw($_POST['referrer'] ?? '');
        $user_agent = sanitize_text_field($_POST['browser'] ?? '');
        $ip         = $_SERVER['REMOTE_ADDR'] ?? '';
        $duration   = intval($_POST['duration'] ?? 0);

        $source = $this->detect_source($referrer);

        $wpdb->insert($table, [
            'session_id' => $session_id,
            'url'        => $url,
            'referrer'   => $referrer,
            'source'     => $source,
            'user_agent' => $user_agent,
            'ip'         => $ip,
            'visit_time' => current_time('mysql'),
            'duration'   => $duration,
        ]);

        wp_send_json_success(['message' => 'Visite enregistrée']);
    }

    private function detect_source($referrer) {
        if (empty($referrer)) return 'direct';
        if (strpos($referrer, $_SERVER['HTTP_HOST']) !== false) return 'direct';
        if (preg_match('/(google|bing|yahoo|duckduckgo)/i', $referrer)) return 'search';
        if (preg_match('/(facebook|twitter|instagram|linkedin|tiktok)/i', $referrer)) return 'social';
        return 'referral';
    }


    /**
     * Fonction appelée à chaque chargement de page frontend
     * Enregistre la visite si nouvelle session ou nouvelle page
     */
    public function track_visit() {
        if (is_admin()) return; // pas dans admin

        global $wpdb;

        $session_id = $this->get_session_id();
        $url = esc_url_raw($_SERVER['REQUEST_URI']);
        $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        $ip = $_SERVER['REMOTE_ADDR'];
        $visit_time = current_time('mysql');
        $source = $this->detect_source($referrer);

        // Vérifier si déjà enregisté cette page dans cette session (évite doublons)
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_visits} WHERE session_id = %s AND url = %s ORDER BY id DESC LIMIT 1",
            $session_id, $url
        ));

        if (!$exists) {
            $wpdb->insert($this->table_visits, [
                'session_id' => $session_id,
                'url' => $url,
                'referrer' => $referrer,
                'source' => $source,
                'user_agent' => $user_agent,
                'ip' => $ip,
                'visit_time' => $visit_time,
                'duration' => 0
            ]);
            // Stocke l'id de la visite courante pour calcul de durée
            setcookie('bba_last_visit_id', $wpdb->insert_id, 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
            $_COOKIE['bba_last_visit_id'] = $wpdb->insert_id;
            // Stocke timestamp de départ dans cookie session (en secondes)
            setcookie('bba_visit_start', time(), 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
            $_COOKIE['bba_visit_start'] = time();
        }
    }

    /**
     * Calcul de la durée de la visite à la fin du script
     * Mise à jour de la durée dans la base si cookie présent
     */
    public function track_duration() {
        if (is_admin()) return;

        if (empty($_COOKIE['bba_last_visit_id']) || empty($_COOKIE['bba_visit_start'])) return;

        global $wpdb;
        $visit_id = intval($_COOKIE['bba_last_visit_id']);
        $start_time = intval($_COOKIE['bba_visit_start']);
        $duration = time() - $start_time;

        // Mise à jour durée (en secondes)
        $wpdb->update(
            $this->table_visits,
            ['duration' => $duration],
            ['id' => $visit_id],
            ['%d'],
            ['%d']
        );
    }

    /**
     * Retourne un identifiant unique de session utilisateur via cookie
     */
    private function get_session_id() {
        if (!isset($_COOKIE['bba_session_id'])) {
            $session_id = bin2hex(random_bytes(32));
            setcookie('bba_session_id', $session_id, time() + 60 * 60 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
            $_COOKIE['bba_session_id'] = $session_id;
        } else {
            $session_id = $_COOKIE['bba_session_id'];
        }
        return $session_id;
    }

    /**
     * Détecte la source à partir du referrer
     */
    
}
