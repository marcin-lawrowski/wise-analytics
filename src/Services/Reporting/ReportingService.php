<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\Services\Commons\DataAccess;

/**
 * Class ReportingService
 * @package Kainex\WiseAnalytics\Services\Reporting
 */
abstract class ReportingService {
	use DataAccess;

	const RESULTS_LIMIT = 20;

	/**
	 * @param array $queryParams
	 * @return \DateTime[]
	 * @throws \Exception
	 */
	protected function getDatesFilters(array $queryParams): array {
		if (!isset($queryParams['filters'])) {
			throw new \Exception('Missing filters');
		}

		$filters = $queryParams['filters'];
		if (!isset($filters['endDate'])) {
			throw new \Exception('Missing filters');
		}
		if (!isset($filters['startDate'])) {
			throw new \Exception('Missing filters');
		}

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

		return [$startDate, $endDate];
	}
}