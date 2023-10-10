<?php

namespace Kainex\WiseAnalytics\Reports;

use Kainex\WiseAnalytics\Reports\Pages\Overview;
use Kainex\WiseAnalytics\Services\Processing\ProcessingService;

class PagesSetup {

	/** @var Overview */
	private $overviewPage;

	/** @var ProcessingService */
	private $processingService;

	/**
	 * PagesSetup constructor.
	 * @param Overview $overviewPage
	 * @param ProcessingService $processingService
	 */
	public function __construct(Overview $overviewPage, ProcessingService $processingService)
	{
		$this->overviewPage = $overviewPage;
		$this->processingService = $processingService;
	}

	public function install() {
		add_menu_page('Analytics', 'Analytics', 'manage_options', 'wise-analytics-overview', '', 'dashicons-chart-bar');
		add_submenu_page('wise-analytics-overview', 'Overview', 'Overview', 'manage_options', 'wise-analytics-overview', array($this, 'overviewAction'));
	}

	public function overviewAction() {
		$this->overviewPage->render();
	}

}