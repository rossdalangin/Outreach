<?php
namespace CloseClient\Outreach\Includes\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Lead {

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'cc_outreach_leads';
    }

    public static function get($id) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    }

    public static function get_by_email($email) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email), ARRAY_A);
    }

    public static function get_by_lead_id($lead_id) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE lead_id = %s", $lead_id), ARRAY_A);
    }

    public static function query($args = array()) {
        global $wpdb;
        $table = self::get_table_name();

        $where = array("1=1");
        $values = array();

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $values[] = $args['status'];
        }

        if (!empty($args['campaign_id'])) {
            $where[] = "campaign_id = %d";
            $values[] = $args['campaign_id'];
        }

        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = "(first_name LIKE %s OR last_name LIKE %s OR company_name LIKE %s OR email LIKE %s OR niche LIKE %s)";
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode(' AND ', $where);
        $limit_sql = "";

        if (isset($args['number']) && $args['number'] > 0) {
            $offset = isset($args['offset']) ? intval($args['offset']) : 0;
            $limit_sql = $wpdb->prepare(" LIMIT %d, %d", $offset, intval($args['number']));
        }

        $orderby = !empty($args['orderby']) ? sanitize_sql_orderby($args['orderby']) : 'id DESC';
        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY $orderby $limit_sql";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function count($args = array()) {
        global $wpdb;
        $table = self::get_table_name();
        $where = array("1=1");
        $values = array();

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $values[] = $args['status'];
        }

        $where_sql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return (int) $wpdb->get_var($sql);
    }

    public static function insert($data) {
        global $wpdb;
        $table = self::get_table_name();

        if (isset($data['custom_data']) && is_array($data['custom_data'])) {
            $data['custom_data'] = json_encode($data['custom_data']);
        }

        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = self::get_table_name();

        if (isset($data['custom_data']) && is_array($data['custom_data'])) {
            $data['custom_data'] = json_encode($data['custom_data']);
        }

        $data['updated_at'] = current_time('mysql');
        return $wpdb->update($table, $data, array('id' => $id));
    }

    public static function update_status($id, $new_status, $action_description = '') {
        $update_data = array('status' => $new_status);
        if (!empty($action_description)) {
            $update_data['last_action'] = $action_description;
        }
        return self::update($id, $update_data);
    }
}
