<?php

namespace Kainex\WiseAnalytics\Services\Reports;

class HighlightsService {

	/** @var UsersReportsService */
	private $usersReportsService;

	/** @var PagesReportsService */
	private $pagesReportsService;

	/**
	 * HighlightsService constructor.
	 * @param UsersReportsService $usersReportsService
	 * @param PagesReportsService $pagesReportsService
	 */
	public function __construct(UsersReportsService $usersReportsService, PagesReportsService $pagesReportsService)
	{
		$this->usersReportsService = $usersReportsService;
		$this->pagesReportsService = $pagesReportsService;
	}

	public function getHighlights(\DateTime $startDate, \DateTime $endDate) {
		$totalVisits = $this->usersReportsService->getTotalUsers($startDate, $endDate);
		$totalPageViews = $this->pagesReportsService->getTotalPageViews($startDate, $endDate);

		return [
			'users' => $totalVisits,
			'pageViews' => $totalPageViews,
			'avgPagesPerVisit' => $totalVisits ? round($totalPageViews / $totalVisits, 2) : 0.0
		];
	}

}