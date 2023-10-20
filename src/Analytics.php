<?php

namespace Kainex\WiseAnalytics;

use Kainex\WiseAnalytics\Services\Users\UsersService;

/**
 * Analytics core.
 *
 * @author Kainex <contact@kainex.pl>
 */
class Analytics {
	
	/** @var Options */
	private $options;

	/**
	 * @param Options $options
	 */
	public function __construct(Options $options) {
		$this->options = $options;
	}

	/**
	 * Registers and enqueues all necessary resources (scripts or styles).
	 */
	public function enqueueResources() {
		$config = [
			'url' => get_site_url(),
			'cookie' => UsersService::UUID_COOKIE
		];
		
		wp_enqueue_script('wise-analytics-libs', plugins_url('assets/js/frontend/wa-libs.js', dirname(__FILE__)), array('jquery'), WISE_ANALYTICS_VERSION, false);
		wp_enqueue_script('wise-analytics-core', plugins_url('assets/js/frontend/wa-core.js', dirname(__FILE__)), array('jquery', 'wise-analytics-libs'), WISE_ANALYTICS_VERSION, false);
		wp_localize_script('wise-analytics-core', 'waConfig', $config);
		wp_enqueue_script('wise-analytics-commons', plugins_url('assets/js/frontend/wa-commons.js', dirname(__FILE__)), array('jquery'), WISE_ANALYTICS_VERSION, false);
	}

	public function enqueueAdminResources() {
		wp_enqueue_script('wise-analytics-admin-vendor', plugins_url('assets/js/admin/wise-analytics-vendor.min.js', dirname(__FILE__)), array('jquery'), WISE_ANALYTICS_VERSION, true);
		wp_enqueue_script('wise-analytics-admin-core', plugins_url('assets/js/admin/wise-analytics.js', dirname(__FILE__)), array('jquery', 'wise-analytics-admin-vendor'), WISE_ANALYTICS_VERSION.filemtime(dirname(__FILE__).'/../assets/js/admin/wise-analytics.js'), true);
		wp_enqueue_style('wise-analytics-core', plugins_url('assets/css/admin/wise-analytics.css', dirname(__FILE__)), array(), WISE_ANALYTICS_VERSION);
		wp_enqueue_style('wise-analytics-core', plugins_url('assets/css/admin/wise-analytics.css', dirname(__FILE__)), array(), WISE_ANALYTICS_VERSION);
	}
	
}