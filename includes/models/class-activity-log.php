<?php
namespace CloseClient\Outreach\Includes\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Activity_Log {

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'cc_outreach_activity_logs';
    }

    public static function log($action, $lead_id = 0, $details = '', $user_id = 0) {
        global $wpdb;
        $table = self::get_table_name();

        if (empty($user_id) && function_exists('get_current_user_id')) {
            $user_id = get_current_user_id();
        }

        if (is_array($details) || is_object($details)) {
            $details = json_encode($details);
        }

        return $wpdb->insert(
            $table,
            array(
                'lead_id'    => intval($lead_id),
                'action'     => sanitize_text_field($action),
                'user_id'    => intval($user_id),
                'details'    => $details,
                'created_at' => current_time('mysql'),
            )
        );
    }

    public static function query($args = array()) {
        global $wpdb;
        $table = self::get_table_name();

        $where = array("1=1");
        $values = array();

        if (!empty($args['lead_id'])) {
            $where[] = "lead_id = %d";
            $values[] = $args['lead_id'];
        }

        $where_sql = implode(' AND ', $where);
        $limit_sql = "";

        if (isset($args['number']) && $args['number'] > 0) {
            $limit_sql = $wpdb->prepare(" LIMIT %d", intval($args['number']));
        }

        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY id DESC $limit_sql";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }
}
