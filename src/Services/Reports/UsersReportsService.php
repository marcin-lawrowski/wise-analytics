<?php

namespace Kainex\WiseAnalytics\Services\Reports;

use Kainex\WiseAnalytics\Services\Commons\DataAccess;

class UsersReportsService {
	use DataAccess;

	public function getTotalUsers(\DateTime $startDate, \DateTime $endDate): int {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents(['COUNT(DISTINCT user_id) AS users'], ["created >= '$startDateStr'", "created <= '$endDateStr'"]);

		return count($result) > 0 ? (int) $result[0]->users : 0;
	}

}