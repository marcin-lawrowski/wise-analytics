<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\Services\Reports\HighlightsService;

class ReportsEndpoint {

	/** @var HighlightsService */
	private $highlightsService;

	/**
	 * ReportsEndpoint constructor.
	 * @param HighlightsService $highlightsService
	 */
	public function __construct(HighlightsService $highlightsService)
	{
		$this->highlightsService = $highlightsService;
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

		try {
			switch ($queryParams['name']) {
				case 'overview.highlights';
					return $this->highlightsService->getHighlights($startDate, $endDate);
			}

		} catch (\Exception $e) {
			return new \WP_Error('endpoint_error', 'Endpoint error: '.$e->getMessage(), ['status' => 500]);
		}

		return new \WP_Error('invalid_report', 'Invalid report', ['status' => 404]);
	}

}