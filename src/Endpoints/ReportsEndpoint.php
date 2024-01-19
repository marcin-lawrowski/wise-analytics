<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\Services\Reporting\Events\EventsReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Highlights\HighlightsService;
use Kainex\WiseAnalytics\Services\Reporting\Pages\PagesReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Sessions\SessionsReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Visitors\VisitorsReportsService;

class ReportsEndpoint {

	/** @var HighlightsService */
	private $highlightsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/** @var VisitorsReportsService */
	private $visitorsReportsService;

	/** @var EventsReportsService */
	private $eventsReportsService;

	/** @var SessionsReportsService */
	private $sessionsReportsService;

	/**
	 * ReportsEndpoint constructor.
	 * @param HighlightsService $highlightsService
	 * @param PagesReportsService $pagesReportsService
	 * @param VisitorsReportsService $visitorsReportsService
	 * @param EventsReportsService $eventsReportsService
	 * @param SessionsReportsService $sessionsReportsService
	 */
	public function __construct(HighlightsService $highlightsService, PagesReportsService $pagesReportsService, VisitorsReportsService $visitorsReportsService, EventsReportsService $eventsReportsService, SessionsReportsService $sessionsReportsService)
	{
		$this->highlightsService = $highlightsService;
		$this->pagesReportsService = $pagesReportsService;
		$this->visitorsReportsService = $visitorsReportsService;
		$this->eventsReportsService = $eventsReportsService;
		$this->sessionsReportsService = $sessionsReportsService;
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
		$offset = $queryParams['offset'] ?? 0;

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
					return $this->pagesReportsService->getTopPagesViews($startDate, $endDate, $offset);
				case 'pages.views.daily';
					return $this->pagesReportsService->getPagesViewsDaily($startDate, $endDate);
				case 'visitors.last';
					return $this->visitorsReportsService->getLastVisitors($startDate, $endDate);
				case 'visitors.daily';
					return $this->visitorsReportsService->getVisitorsDaily($startDate, $endDate);
				case 'visitors.languages';
					return $this->visitorsReportsService->getLanguages($startDate, $endDate);
				case 'sessions.daily';
					return $this->sessionsReportsService->getSessionsDaily($startDate, $endDate);
				case 'events';
					return $this->eventsReportsService->getEvents($startDate, $endDate, $offset);
			}

		} catch (\Exception $e) {
			return new \WP_Error('endpoint_error', 'Endpoint error: '.$e->getMessage(), ['status' => 500]);
		}

		return new \WP_Error('invalid_report', 'Invalid report', ['status' => 404]);
	}

}