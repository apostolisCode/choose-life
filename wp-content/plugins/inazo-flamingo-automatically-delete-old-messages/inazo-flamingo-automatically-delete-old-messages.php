<?php
/*
Plugin Name: Inazo's flamingo automatically delete old messages
Description: This plugin help you to auto removed all information stored by flamingo.
Version:     1.2
Author:      Inazo
Author URI:  https://www.kanjian.fr
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
*/


/*
 * Pour des raisons de sécurité aucun code ne doit être placé au dessus de cette ligne
 */
if( !defined( 'ABSPATH' ) ){
	
	exit(-1);
}


class inazo_flamingo_auto_trash{
	
	const VERSION_IAAM = '1.2';
	const PLUGIN_NAME  = 'inazo_flamingo_auto_trash';
	
		
	function __construct(){
		
		add_action('admin_menu', array($this, 'createAdminMenu') );		
		add_action('inazo_flamingo_auto_trash_cron_task_daily', array($this, 'inazo_flamingo_auto_trash_cron_task'));
		
		
		if (! wp_next_scheduled ( 'inazo_flamingo_auto_trash_cron_task_daily' )){
		    wp_schedule_event(time(), 'hourly', 'inazo_flamingo_auto_trash_cron_task_daily');
		}
	}
	
	/*
	 * Create the install of the plugin
	 */
	function installPlugin(){
		
		add_option( self::PLUGIN_NAME.'_version', self::VERSION_IAAM );
		
		// set default value before auto trash
		add_option( self::PLUGIN_NAME.'_contact_days', 365 );
		add_option( self::PLUGIN_NAME.'_inbound_days', 365 );
		add_option( self::PLUGIN_NAME.'_outbound_days', 365 );
		add_option( self::PLUGIN_NAME.'_spam_days', 30 );
				
	}
	
	/*
	 * Création du menu dans le back office de Wordpress
	 */
	function createAdminMenu(){
		
		add_options_page( 'Flamingo auto trash', 'Flamingo auto trash', 'edit_posts', 'inazo.flamingo.auto.trash.config', array($this, 'configAction') );
	}

	/**
	 * Add option if it was delete, or update it
	 * 
	 */

	function check_option_exists_and_update( $option_name, $new_value ){
	    
	    if ( get_option( $option_name ) !== false ) {
	        
	        update_option( $option_name, $new_value );
	        
	    } else {
	        
	        add_option( $option_name, $new_value);
	    }
	}
	
	/*
	 * Controller pour ajouter un ads
	 */
	function configAction() {
		
		//-- on est sur la sauvegarde de la publicité
		if ( ! empty( $_POST ) && check_admin_referer( 'inazo.flamingo.auto.trash.config', 'token_csrf_action_edit' ) ) {
		
		    $this->check_option_exists_and_update( self::PLUGIN_NAME.'_contact_days', (integer) $_POST['days_contact'] );
		    $this->check_option_exists_and_update( self::PLUGIN_NAME.'_inbound_days', (integer) $_POST['days_inbound'] );
		    $this->check_option_exists_and_update( self::PLUGIN_NAME.'_outbound_days', (integer) $_POST['days_outbound'] );
		    $this->check_option_exists_and_update( self::PLUGIN_NAME.'_spam_days', (integer) $_POST['days_spam'] );
		    
		    include('view/modif-ok.php');
		}
		
		include('view/add.php');
	}
	

	/*
	 * inazo_flamingo_auto_trash_cron_task
	 * */
	function inazo_flamingo_auto_trash_cron_task() {
	
		global $wpdb;
	
		/*
		 * Suppression des contacts
		 */
		$nb_days_contact = get_option(self::PLUGIN_NAME.'_contact_days','365');
		
		$sql = $wpdb->prepare('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_type="flamingo_contact" AND 
        ( TIMESTAMPDIFF(DAY, post_date, NOW()) ) >= %d', $nb_days_contact);
		
		$wpdb->query( $sql );		
		
		if( $wpdb->num_rows > 0 ){
		    
		    foreach( $wpdb->get_results($sql, ARRAY_A) as $k => $v ){
		        
		          wp_delete_post($v['ID'], true);    
		    }
		}
		
		/**
		 * Suppression des inbound
		 */
		$nb_days_inbound = get_option(self::PLUGIN_NAME.'_inbound_days','365');
		
		$sql = $wpdb->prepare('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_type="flamingo_inbound" AND
        ( TIMESTAMPDIFF(DAY, post_date, NOW()) ) >= %d', $nb_days_inbound);
		
		$wpdb->query( $sql );
		
		if( $wpdb->num_rows > 0 ){
		    
		    foreach( $wpdb->get_results($sql, ARRAY_A) as $k => $v ){
		        
		        wp_delete_post($v['ID'], true);
		    }
		}
		
		/**
		 * Suppression des outbound
		 */
		$nb_days_outbound = get_option(self::PLUGIN_NAME.'_outbound_days','365');
		
		$sql = $wpdb->prepare('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_type="flamingo_outbound" AND
        ( TIMESTAMPDIFF(DAY, post_date, NOW()) ) >= %d', $nb_days_outbound);
		
		$wpdb->query( $sql );
		
		if( $wpdb->num_rows > 0 ){
		    
		    foreach( $wpdb->get_results($sql, ARRAY_A) as $k => $v ){
		        
		        wp_delete_post($v['ID'], true);
		    }
		}
		
		/**
		 * Deleting spam
		 */
		$nb_days_spam = get_option(self::PLUGIN_NAME.'_spam_days','365');
		
		$sql = $wpdb->prepare('
        SELECT 
            DISTINCT(ID) 
        FROM '.$wpdb->prefix.'posts 

        INNER JOIN '.$wpdb->prefix.'postmeta as pm

        WHERE post_type="flamingo_inbound" 
        AND ( TIMESTAMPDIFF(DAY, post_date, NOW()) ) >= %d
        AND ( (pm.meta_key = "_submission_status" AND pm.meta_value ="spam") OR post_status="flamingo-spam" )
        LIMIT 0, 10000
        ', $nb_days_spam);
		
		$wpdb->query( $sql );
		
		if( $wpdb->num_rows > 0 ){
		    
		    foreach( $wpdb->get_results($sql, ARRAY_A) as $k => $v ){
		        
		        wp_delete_post($v['ID'], true);
		    }
		}
	}
	

	function unactivatePlugin(){
	
		wp_clear_scheduled_hook('inazo_flamingo_auto_trash_cron_task_daily');
	}	
}


//-- déclaration de l'objet
$inazo_flamingo_auto_trash = new inazo_flamingo_auto_trash();
register_activation_hook( __FILE__, array( &$inazo_flamingo_auto_trash, 'installPlugin' ) );
register_deactivation_hook(__FILE__, array( &$inazo_flamingo_auto_trash, 'unactivatePlugin') );
