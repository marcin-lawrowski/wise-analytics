<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\Services\Reporting\Events\EventsReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Highlights\HighlightsService;
use Kainex\WiseAnalytics\Services\Reporting\Pages\PagesReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Visitors\VisitorsReportsService;

class ReportsEndpoint {

	/** @var HighlightsService */
	private $highlightsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/** @var VisitorsReportsService */
	private $usersReportsService;

	/** @var EventsReportsService */
	private $eventsReportsService;

	/**
	 * ReportsEndpoint constructor.
	 * @param HighlightsService $highlightsService
	 * @param PagesReportsService $pagesReportsService
	 * @param VisitorsReportsService $usersReportsService
	 * @param EventsReportsService $eventsReportsService
	 */
	public function __construct(HighlightsService $highlightsService, PagesReportsService $pagesReportsService, VisitorsReportsService $usersReportsService, EventsReportsService $eventsReportsService)
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

		try {
			$startDate = \DateTime::createFromFormat("Y-m-d", $filters['startDate']);
			$endDate = \DateTime::createFromFormat("Y-m-d", $filters['endDate']);
			if ($startDate === false || $endDate === false) {
				throw new \Exception('Invalid dates format');
			}

			$startDate->setTime(0, 0, 0);
			$endDate->setTime(23, 59, 59);

			if ($startDate >= $endDate) {
				throw new \Exception('Invalid dates');
			}

			switch ($queryParams['name']) {
				case 'overview.highlights';
					return $this->highlightsService->getHighlights($startDate, $endDate);
				case 'pages.top';
					return $this->pagesReportsService->getTopPagesViews($startDate, $endDate);
				case 'visitors.last';
					return $this->usersReportsService->getLastVisitors($startDate, $endDate);
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