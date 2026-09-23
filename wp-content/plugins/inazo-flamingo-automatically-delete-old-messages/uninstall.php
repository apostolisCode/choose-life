<?php

if( !defined( 'ABSPATH' ) ){

	exit(-1);
}


if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ){
    
    exit();
}
    
delete_option('inazo_flamingo_auto_trash_version');
delete_option('inazo_flamingo_auto_trash_contact_days');
delete_option('inazo_flamingo_auto_trash_inbound_days');
delete_option('inazo_flamingo_auto_trash_outbound_days');
delete_option('inazo_flamingo_auto_trash_spam_days');

wp_clear_scheduled_hook('inazo_flamingo_auto_trash_cron_task_daily');