<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\Services\Reporting\EventsReportsService;
use Kainex\WiseAnalytics\Services\Reporting\HighlightsService;
use Kainex\WiseAnalytics\Services\Reporting\PagesReportsService;
use Kainex\WiseAnalytics\Services\Reporting\UsersReportsService;

class ReportsEndpoint {

	/** @var HighlightsService */
	private $highlightsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/** @var UsersReportsService */
	private $usersReportsService;

	/** @var EventsReportsService */
	private $eventsReportsService;

	/**
	 * ReportsEndpoint constructor.
	 * @param HighlightsService $highlightsService
	 * @param PagesReportsService $pagesReportsService
	 * @param UsersReportsService $usersReportsService
	 * @param EventsReportsService $eventsReportsService
	 */
	public function __construct(HighlightsService $highlightsService, PagesReportsService $pagesReportsService, UsersReportsService $usersReportsService, EventsReportsService $eventsReportsService)
	{
		$this->highlightsService = $highlightsService;
		$this->pagesReportsService = $pagesReportsService;
		$this->usersReportsService = $usersReportsService;
		$this->eventsReportsService = $eventsReportsService;
	}

	public function registerEndpoints() {
		register_rest_route( 'wise-analytics/v1', '/report', array(
			'methods' => 'GET',
			'callback' => [$this, 'reportEndpoint'],
			'permission_callback' => function() {
				return true; // TODO: check the permissions
			}
		));
	}

	// TODO: report all errors through exceptions
	public function reportEndpoint(\WP_REST_Request $request) {
		$queryParams = $request->get_query_params();
		$filters = $queryParams['filters'];

		$startDate = \DateTime::createFromFormat("Y-m-d", $filters['startDate']);
		$endDate = \DateTime::createFromFormat("Y-m-d", $filters['endDate']);
		$startDate->setTime(0, 0, 0);
		$endDate->setTime(23, 59, 59);

		// TODO: validate dates

		try {
			switch ($queryParams['name']) {
				case 'overview.highlights';
					return $this->highlightsService->getHighlights($startDate, $endDate);
				case 'overview.pages.top';
					return $this->pagesReportsService->getTopPagesViews($startDate, $endDate);
				case 'visitors';
					return $this->usersReportsService->getVisitors($startDate, $endDate);
				case 'visitors.daily';
					return $this->usersReportsService->getVisitorsDaily($startDate, $endDate);
				case 'events';
					return $this->eventsReportsService->getEvents($startDate, $endDate);
			}

		} catch (\Exception $e) {
			return new \WP_Error('endpoint_error', 'Endpoint error: '.$e->getMessage(), ['status' => 500]);
		}

		return new \WP_Error('invalid_report', 'Invalid report', ['status' => 404]);
	}

}