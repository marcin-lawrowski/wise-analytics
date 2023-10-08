<?php


namespace Kainex\WiseAnalytics\Services\Reporting;


use Kainex\WiseAnalytics\Services\Commons\DataAccess;

class SessionsReportsService {
	use DataAccess;

	public function getAverageTime(\DateTime $startDate, \DateTime $endDate): int {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'select' => ['SUM(duration) / COUNT(*) as avgSessionTime'],
			'where' => ["start >= '$startDateStr'", "start <= '$endDateStr'"]
		]);

		return count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;
	}

}