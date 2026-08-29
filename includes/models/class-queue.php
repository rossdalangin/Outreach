<?php
namespace CloseClient\Outreach\Includes\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Queue {

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'cc_outreach_queue';
    }

    public static function get($id) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
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

        if (!empty($args['lead_id'])) {
            $where[] = "lead_id = %d";
            $values[] = $args['lead_id'];
        }

        $where_sql = implode(' AND ', $where);
        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY id DESC";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function insert($data) {
        global $wpdb;
        $table = self::get_table_name();
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }

    public static function update($id, $data) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->update($table, $data, array('id' => $id));
    }

    public static function count_by_status($status) {
        global $wpdb;
        $table = self::get_table_name();
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE status = %s", $status));
    }
}
