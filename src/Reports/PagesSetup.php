<?php

namespace Kainex\WiseAnalytics\Reports;

use Kainex\WiseAnalytics\Reports\Pages\Overview;

class PagesSetup {

	/** @var Overview */
	private $overviewPage;

	/**
	 * PagesSetup constructor.
	 * @param Overview $overviewPage
	 */
	public function __construct(Overview $overviewPage)
	{
		$this->overviewPage = $overviewPage;
	}

	public function install() {
		add_menu_page('Analytics', 'Analytics', 'manage_options', 'wise-analytics-overview', '', 'dashicons-chart-bar');
		add_submenu_page('wise-analytics-overview', 'Overview', 'Overview', 'manage_options', 'wise-analytics-overview', array($this, 'overviewAction'));
	}

	public function overviewAction() {
		$this->overviewPage->render();
	}

}