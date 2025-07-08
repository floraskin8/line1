<?php
class BlackBeast_Tracker {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_script']);
        add_action('wp_ajax_bba_track_visit', [$this, 'track_visit']);
        add_action('wp_ajax_nopriv_bba_track_visit', [$this, 'track_visit']);
        add_action('wp_ajax_bba_track_duration', [$this, 'track_duration']);
        add_action('wp_ajax_nopriv_bba_track_duration', [$this, 'track_duration']);
    }

    public function enqueue_script() {
        wp_enqueue_script('bba-tracking', BBA_URL . 'assets/js/tracking.js', [], '1.0', true);
        wp_localize_script('bba-tracking', 'blackbeast_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function track_visit() {
        global $wpdb;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['session_id'])) wp_die();

        $ip = $_SERVER['REMOTE_ADDR'];
        $geo = $this->get_geo($ip);

        $wpdb->insert($wpdb->prefix . 'blackbeast_visits', [
            'session_id' => sanitize_text_field($input['session_id']),
            'url' => esc_url_raw($input['url']),
            'referrer' => esc_url_raw($input['referrer']),
            'lang' => sanitize_text_field($input['lang']),
            'browser' => sanitize_text_field($input['browser']),
            'country' => sanitize_text_field($geo['country']),
            'city' => sanitize_text_field($geo['city']),
        ]);

        wp_die();
    }

    public function track_duration() {
        global $wpdb;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['session_id'])) wp_die();

        $wpdb->update(
            $wpdb->prefix . 'blackbeast_visits',
            ['duration' => intval($input['duration'])],
            ['session_id' => sanitize_text_field($input['session_id'])],
            ['%d'],
            ['%s']
        );

        wp_die();
    }

    private function get_geo($ip) {
        // Placeholder pour la géolocalisation locale
        // À remplacer par une vraie détection IP locale (GeoLite2, CSV...)
        return [
            'country' => 'Unknown',
            'city' => 'Unknown'
        ];
    }
}
