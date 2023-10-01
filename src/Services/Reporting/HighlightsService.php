<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\Utils\TimeUtils;

class HighlightsService {

	/** @var UsersReportsService */
	private $usersReportsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/** @var SessionsReportsService */
	private $sessionsReportsService;

	/**
	 * HighlightsService constructor.
	 * @param UsersReportsService $usersReportsService
	 * @param PagesReportsService $pagesReportsService
	 * @param SessionsReportsService $sessionsReportsService
	 */
	public function __construct(UsersReportsService $usersReportsService, PagesReportsService $pagesReportsService, SessionsReportsService $sessionsReportsService)
	{
		$this->usersReportsService = $usersReportsService;
		$this->pagesReportsService = $pagesReportsService;
		$this->sessionsReportsService = $sessionsReportsService;
	}

	public function getHighlights(\DateTime $startDate, \DateTime $endDate) {
		$totalVisits = $this->usersReportsService->getTotalUsers($startDate, $endDate);
		$totalPageViews = $this->pagesReportsService->getTotalPageViews($startDate, $endDate);
		$avgSessionTime = $this->sessionsReportsService->getAverageTime($startDate, $endDate);

		return [
			'users' => $totalVisits,
			'pageViews' => $totalPageViews,
			'avgPagesPerVisit' => $totalVisits ? round($totalPageViews / $totalVisits, 2) : 0.0,
			'avgSessionTime' => $avgSessionTime > 0 ? TimeUtils::formatDuration($avgSessionTime, 'suffixes') : '0s'
		];
	}

}