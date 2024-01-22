<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Highlights;

use Kainex\WiseAnalytics\Services\Reporting\Pages\PagesReportsService;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Services\Reporting\Sessions\SessionsReportsService;
use Kainex\WiseAnalytics\Services\Reporting\Visitors\VisitorsReportsService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class HighlightsService extends ReportingService {

	/** @var VisitorsReportsService */
	private $visitorsReportsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/** @var SessionsReportsService */
	private $sessionsReportsService;

	/**
	 * HighlightsService constructor.
	 * @param VisitorsReportsService $usersReportsService
	 * @param PagesReportsService $pagesReportsService
	 * @param SessionsReportsService $sessionsReportsService
	 */
	public function __construct(VisitorsReportsService $usersReportsService, PagesReportsService $pagesReportsService, SessionsReportsService $sessionsReportsService)
	{
		$this->visitorsReportsService = $usersReportsService;
		$this->pagesReportsService = $pagesReportsService;
		$this->sessionsReportsService = $sessionsReportsService;
	}

	public function getHighlights(array $queryParams) {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$totalVisits = $this->visitorsReportsService->getVisitorsHighlights($startDate, $endDate);
		$totalPageViews = $this->pagesReportsService->getTotalPageViews($startDate, $endDate);
		$avgSessionTime = $this->sessionsReportsService->getAverageTime($startDate, $endDate);

		return [
			'visitors' => $totalVisits,
			'pageViews' => $totalPageViews,
			'avgPagesPerVisit' => $totalVisits['total'] ? round($totalPageViews / $totalVisits['total'], 2) : 0.0,
			'avgSessionTime' => $avgSessionTime > 0 ? TimeUtils::formatDuration($avgSessionTime, 'suffixes') : '0s'
		];
	}

}