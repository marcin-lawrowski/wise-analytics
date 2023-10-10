<?php

namespace Kainex\WiseAnalytics;

/**
 * Installer
 *
 * @author Kainex <contact@kainex.pl>
 */
class Installer {

	/**
	 * Installs all plugin activation hooks.
	 *
	 * @param string $pluginFile
	 */
	public static function setup(string $pluginFile) {
		register_activation_hook($pluginFile, [Installer::class, 'activate']);
		register_deactivation_hook($pluginFile, [Installer::class, 'deactivate']);
		register_uninstall_hook($pluginFile, [Installer::class, 'uninstall']);
		add_action('wpmu_new_blog', [Installer::class, 'newBlog'], 10, 6);
		add_action('delete_blog', [Installer::class, 'deleteBlog'], 10, 6);
	}

	public static function getUsersTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_users';
	}

	public static function getEventTypesTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_event_types';
	}

	public static function getEventResourcesTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_resources';
	}

	public static function getMetricsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_metrics';
	}

	public static function getEventsTable() {
		global $wpdb;
		
		return $wpdb->prefix.'wise_analytics_events';
	}

	public static function getSessionsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_sessions';
	}

	public static function getDailyStatsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_stats_daily';
	}

	public static function getWeeklyStatsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_stats_weekly';
	}

	public static function getMonthlyStatsTable() {
		global $wpdb;

		return $wpdb->prefix.'wise_analytics_stats_monthly';
	}

	/**
	 * Plugin's activation action. Creates database structure (if does not exist), upgrades database structure and
	 * initializes options. Supports WordPress multisite.
	 *
	 * @param boolean $networkWide True if it is a network activation - if so, run the activation function for each blog id
	 */
	public static function activate($networkWide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkWide) {
				$oldBlogID = $wpdb->blogid;
				$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogIDs as $blogID) {
					switch_to_blog($blogID);
					self::doActivation();
				}
				switch_to_blog($oldBlogID);
				return;
			}
		}
		self::doActivation();
	}

	/**
	 * Executed when admin creates a site in mutisite installation.
	 *
	 * @param integer $blogID
	 * @param integer $userID
	 * @param string $domain
	 * @param string $path
	 * @param string $siteID
	 * @param mixed $meta
	 */
	public static function newBlog($blogID, $userID, $domain, $path, $siteID, $meta) {
		global $wpdb;

		if (is_plugin_active_for_network('wise-analytics/wise-analytics-core.php')) {
			$oldBlogID = $wpdb->blogid;
			switch_to_blog($blogID);
			self::doActivation();
			switch_to_blog($oldBlogID);
		}
	}

	/**
	 * Executed when admin deletes a site in mutisite installation.
	 *
	 * @param int $blogID Blog ID
	 * @param bool $drop True if blog's table should be dropped. Default is false.
	 */
	public static function deleteBlog($blogID, $drop) {
		global $wpdb;

		$oldBlogID = $wpdb->blogid;
		switch_to_blog($blogID);
		self::doUninstall('deleteblog_'.$blogID);
		switch_to_blog($oldBlogID);
	}

	private static function doActivation() {
		global $wpdb, $user_level, $sac_admin_user_level;
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}

		$charsetCollate = $wpdb->get_charset_collate();

		$tableName = self::getUsersTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				uuid text NOT NULL,
				created datetime NOT NULL default now(),
				data json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getEventResourcesTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				type_id bigint(11),
				text_key text,
				text_value text,
				int_key int,
				int_value int,
				created datetime not null default now()
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		$tableName = self::getEventsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id bigint(11),
				type_id bigint(11),
				uri text,
				created datetime not null default now(),
				checksum text,
				data json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getSessionsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id bigint(11),
				start datetime not null,
				end datetime not null,
				duration int default 0,
				events json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getEventTypesTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name text NOT NULL,
				slug text NOT NULL,
				data json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getMetricsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name text NOT NULL,
				slug text NOT NULL
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getDailyStatsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				time_dimension datetime not null,
				metric_id bigint(11),
				metric_int_value bigint(11),
				dimensions json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getWeeklyStatsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				time_dimension datetime not null,
				metric_id bigint(11),
				metric_int_value bigint(11),
				dimensions json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$tableName = self::getMonthlyStatsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				time_dimension datetime not null,
				metric_id bigint(11),
				metric_int_value bigint(11),
				dimensions json
		) $charsetCollate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		self::installCron();
		
		// set default options after installation:
		//$settings = WiseAnalyticsContainer::get('WiseAnalyticsSettings');
		//$settings->setDefaultSettings();
	}

	/**
	 * Plugin's deactivation action.
	 */
	public static function deactivate() {
		global $wpdb, $user_level, $sac_admin_user_level;
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}

		if (function_exists('is_multisite') && is_multisite()) {
			$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogIDs as $blogID) {
				self::uninstallCron($blogID);
			}
			return;
		}

		self::uninstallCron($wpdb->blogid);
	}

	/**
	 * Plugin's uninstall action. Deletes all database tables and plugin's options.
	 * Supports WordPress multisite.
	 */
	public static function uninstall() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			$oldBlogID = $wpdb->blogid;
			$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogIDs as $blogID) {
				switch_to_blog($blogID);
				self::doUninstall();
			}
			switch_to_blog($oldBlogID);
			return;
		}
		self::doUninstall();
	}

	private static function doUninstall($refererCheck = null) {
		if (!current_user_can('activate_plugins')) {
			return;
		}
		if ($refererCheck !== null) {
			check_admin_referer($refererCheck);
		}
        
        global $wpdb;
		
		$tableName = self::getEventTypesTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getEventsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getSessionsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getUsersTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getDailyStatsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getWeeklyStatsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getMonthlyStatsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getMetricsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getEventResourcesTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		//WiseAnalyticsOptions::getInstance()->dropAllOptions();
	}

	private static function installCron() {
		if (!wp_next_scheduled('wa_processing_hook_'.get_current_blog_id())) {
			wp_schedule_event(time(), 'hourly', 'wa_processing_hook_'.get_current_blog_id());
		}
	}

	private static function uninstallCron($blogId) {
		$timestamp = wp_next_scheduled('wa_processing_hook_'.$blogId);
        wp_unschedule_event($timestamp, 'wa_processing_hook_'.$blogId);
	}

}