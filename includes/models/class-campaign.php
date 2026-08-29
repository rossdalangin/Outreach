<?php
namespace CloseClient\Outreach\Includes\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Campaign {

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'cc_outreach_campaigns';
    }

    public static function get($id) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    }

    public static function query($args = array()) {
        global $wpdb;
        $table = self::get_table_name();
        return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);
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
}
