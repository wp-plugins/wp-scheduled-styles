<?php
/*
 * This script removes the table for this plugin when the plugin is deleted
 */
global $wpdb;
$table_name = $wpdb->prefix ."scheduledstyles";
$sql = "DROP TABLE $table_name;";
$wpdb->query($sql);
?>