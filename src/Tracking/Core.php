<?php

namespace Kainex\WiseAnalytics\Tracking;

use Kainex\WiseAnalytics\Options;

/**
 * Core tracking code.
 *
 * @author Kainex <contact@kainex.pl>
 */
class Core {
	
	/** @var Options */
	private $options;

	/**
	 * @param Options $options
	 */
	public function __construct(Options $options) {
		$this->options = $options;
	}

	public function install() {
		add_action('wp_footer', array($this, 'printPageViewTrackingCode'));
	}
	
	/**
	 * Prints JS tracking code for all non-404 pages.
     */
	public function printPageViewTrackingCode() {
		if (!is_404()) {
			$this->printTrackingCode('page-view');
		}
	}
	
	private function printTrackingCode($eventType, $customData = array()) {
		$customDataParameter = '';
		if (count($customData) > 0) {
			$customDataParameter = ','.json_encode($customData);
		}
		
		echo '<script type="text/javascript">wa.track("'.esc_html($eventType).'"'.esc_html($customDataParameter).');</script>';
	}
	
}