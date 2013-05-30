<?php
/*
Plugin Name: Simpul Missed Schedule by Esotech
Version: 1.0
Author: Esotech - Alexander Conroy - Geilt
Author URI: http://www.geilt.com
Description: Checks for Missed Schedules Posts and Posts them.
Requires at least: 3.5.1
Tested up to: 3.5.1
License: MIT
 */
$simpulMissedSchedule = new SimpulMissedSchedule;

class SimpulMissedSchedule {
    const SIMPUL_MS_DELAY = 1;
    const SIMPUL_MS_OPTION = 'simpul_missed_schedule';
    public function __construct(){
        add_action('init', array($this, 'init'));
        register_deactivation_hook(__FILE__, array($this, 'cleanup'));
    }
    private function init() {
        global $wpdb;
        
        $last = get_option(SELF::SIMPUL_MS_OPTION, false);
        if (($last !== false) && ($last > ( current_time('timestamp') - (SELF::SIMPUL_MS_DELAY * 60)))) return;
        
        update_option(SELF::SIMPUL_MS_OPTION, current_time('timestamp'));
        
        $query = $wpdb->prepare(
            "SELECT
                ID 
                FROM ' . $wpdb->prefix . 'posts 
                WHERE
                post_status = 'future' 
                AND ( post_date <= %s OR post_date_gmt <= %s )
                LIMIT 10",
            current_time('mysql'),
            current_time('mysql', 1)
        );
        $scheduledIDs = $wpdb->get_col( $query );
        if (!count( $scheduledIDs )) return;
        foreach( $scheduledIDs as $scheduledID ):
            if ( !$scheduledID ):
                continue;
            else:
                wp_publish_post( $scheduledID );
            endif;
        endforeach;
    }
    private function cleanup(){
        delete_option(SELF::SIMPUL_MS_OPTION);
    }
}
