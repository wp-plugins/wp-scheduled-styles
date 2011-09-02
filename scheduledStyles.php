<?php
/*
 * Plugin Name: WP Scheduled Styles
 * Plugin URI: http://www.itegritysolutions.ca/community/wordpress/scheduled-styles
 * Description: Schedule a css file for use on the live site for holidays or special events.
 * Author: Adam Erstelle
 * Version: 1.0.1
 * Author URI: http://www.itegritysolutions.ca/
 * 
 * PLEASE NOTE: If you make any modifications to this plugin file directly, please contact me so that
 *              the plugin can be updated for others to enjoy the same freedom and functionality you
 *              are trying to add. Thank you!
 *
 * Copyright 2011  Adam Erstelle
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if(!class_exists('ScheduledStyles')){
	class ScheduledStyles{
		var $cssFilesToInclude=array();
		var $pluginURL;
		var $pluginDIR;
		var $activeStyle;
		
		/**
		 * PHP4 Constructor
		 */
		function ScheduledStyles(){$this->__construct();}
		
		/**
		 * Class Constructor, Checks to see if style sheets should be included and registers the plugin with Wordpress
		 */
		function __construct(){
			$this->pluginURL = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__));
			$this->pluginDIR = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__));
			//$this->query_wordpress_apis();
			$this->determine_styles_to_include();
			$this->register_hooks();
		}
		
		/**
		 * Makes the calls into the Wordpress API so that results are 'cached' and APIs only called once
		 */
		function query_wordpress_apis(){
			//TODO
		}
		
		/**
		 * Centralized place for adding all actions and filters for the plugin into wordpress
		 */
		function register_hooks(){
			if(is_admin()){
				register_activation_hook(__FILE__, array(&$this,'install'));
				add_action('admin_menu', array(&$this,'admin_menu_link'));
				$this->enqueueItems();
			}
			else{
				add_action('wp_head', array(&$this,'wp_head'));
				add_action('wp_print_styles',array(&$this,'enqueue_styles'));
			}
		}
			
		/**
		 * Adds html comments to HEAD on the public site
		 */
		function wp_head(){
			echo "\n<!-- WP Scheduled Styles is installed -->\n";
		}
		
		/**
		 * Determines which styles should be included in the HEAD of the current theme
		 */
		function determine_styles_to_include(){
			$styleSchedule = $this->read_schedule('current');
			
			foreach($styleSchedule as $style)
			{
				array_push($this->cssFilesToInclude, $style->cssFile);
			}
		}		
		
		function enqueue_styles()
		{
			$id=0;
			foreach($this->cssFilesToInclude as $cssFile)
			{
				$id++;
				$cssFilePath=get_bloginfo('stylesheet_directory')."/$cssFile";
				wp_enqueue_style("ScheduledStyle$id",$cssFilePath);
			}
		}
		
		/**
		 * Reads the schedule from the database for a particular status
		 * @param string $status
		 * @return Ambigous <mixed, NULL, multitype:, unknown>
		 */
		function read_schedule($status){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledstyles";
			$where = "WHERE status='$status'";
			if($status=='current')//this is ugly, will refactor later
				$where = "WHERE status='active' AND now() BETWEEN startTime AND endTime ";
			
			$sql="SELECT id,date_format(startTime,'%Y-%m-%d') as startTime,date_format(endTime,'%Y-%m-%d') as endTime,cssFile,repeatYearly FROM $tableName $where ORDER BY startTime;";
			$results = $wpdb->get_results($sql);
			return $results;
		}
		
		/**
		 * Saves the schedule to the database
		 */
		function save_schedule(){
			global $wpdb;
			$tableName = $wpdb->prefix ."scheduledstyles";
			
			if($_POST['itemKeys'])
				foreach ($_POST['itemKeys'] as $postKey){
					$cssFile=$_POST["items$postKey-stylesheet"];
					$startTime=$_POST["items$postKey-startTime"] . ' 00:00:00';
					$endTime=$_POST["items$postKey-endTime"] . ' 23:59:59';
					$repeat=0;
					if($_POST["items$postKey-repeatYearly"]=='on')
						$repeat=1;
					$isToDelete=$_POST["items$postKey-delete"];
					
					if(!$isToDelete==1)
						$sql = "UPDATE $tableName SET cssFile='$cssFile', startTime='$startTime', endTime='$endTime', repeatYearly='$repeat' WHERE id=$postKey;";
					else
						$sql = "DELETE FROM $tableName WHERE id=$postKey;";
					
					$wpdb->query($sql);
				}
			if($_POST['newStyleKeys'])
				foreach ($_POST['newStyleKeys'] as $newKey){
					$cssFile=$_POST["newStyle$newKey-stylesheet"];
					$startTime=$_POST["newStyle$newKey-startTime"] . ' 00:00:00';
					$endTime=$_POST["newStyle$newKey-endTime"] . ' 23:59:59';
					$repeat=0;
					if($_POST["newStyle$newKey-repeatYearly"]=='on')
						$repeat=1;
					
					$sql = "INSERT INTO $tableName (cssFile,startTime,endTime,repeatYearly) values ('$cssFile','$startTime','$endTime',$repeat);";
					
					$wpdb->query($sql);
				}
		}
		
		/**
		 * Installs the table into the database
		 */
		function install(){			
			global $wpdb;
			$table_name = $wpdb->prefix ."scheduledstyles";
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'")!= $table_name){
				$sql = "CREATE TABLE $table_name (
					id mediumint(3) NOT NULL AUTO_INCREMENT,
					startTime datetime NOT NULL,
					endTime datetime NOT NULL,
					cssFile varchar(100) NOT NULL,
					repeatYearly tinyint(1) NOT NULL,
					status char(10) NOT NULL DEFAULT 'active',
					UNIQUE KEY id (id)
					);";
				require_once(ABSPATH.'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				add_option("scheduledstyles_db_version","1.0");
			}
		}
		
		/**
		 * Adds the Administration link under Appearance in the Wordpress Menu for Administrators
		 */
		function admin_menu_link(){
			add_theme_page('Scheduled Styles', 'Scheduled Styles', 'administrator', basename(__FILE__), array(&$this,'admin_options_page'));
		}
	
		/**
		 * Adds the javascript and CSS to the administration page
		 */
		function enqueueItems(){
			wp_enqueue_script('datepickerScript',$this->pluginURL .'/js/jquery-ui-datepicker.js',array('jquery','jquery-ui-core'));
			wp_enqueue_script('jQueryValidator',$this->pluginURL .'/js/jquery.validate.min.js',array('jquery'));
			wp_enqueue_script('scheduledStylesScript',$this->pluginURL .'/js/scheduledStyles.js',array('jquery','jquery-ui-core','datepickerScript','jQueryValidator'));
			
			wp_enqueue_style('datepickerStyle',$this->pluginURL .'/css/jquery-ui-1.8.11.custom.css');
			wp_enqueue_style('scheduledStylesStyle',$this->pluginURL .'/css/scheduledStyles.css');
		}
		
		/**
		 * Displays the administration page
		 */
		function admin_options_page(){
			if($_POST['_wpnonce'] && wp_verify_nonce($_POST['_wpnonce'], 'scheduledStylesNonceField'))
				$this->save_schedule();
				
			require_once($this->pluginDIR .'/adminPage.php');
		}
	}
}
$scheduledStyles = new ScheduledStyles();
?>