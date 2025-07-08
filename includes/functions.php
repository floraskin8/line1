<?php
function bba_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $visits_table = $wpdb->prefix . 'blackbeast_visits';
    $events_table = $wpdb->prefix . 'blackbeast_events';

    $sql = "
        CREATE TABLE IF NOT EXISTS $visits_table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64),
            url TEXT,
            referrer TEXT,
            lang VARCHAR(10),
            browser VARCHAR(255),
            country VARCHAR(64),
            city VARCHAR(64),
            duration INT DEFAULT 0,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;

        CREATE TABLE IF NOT EXISTS $events_table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64),
            event_type VARCHAR(50),
            label TEXT,
            value TEXT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function bba_uninstall() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}blackbeast_visits");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}blackbeast_events");
}
