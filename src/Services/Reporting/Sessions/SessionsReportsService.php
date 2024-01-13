<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Sessions;

use Kainex\WiseAnalytics\Services\Reporting\ReportingService;

class SessionsReportsService extends ReportingService {

	public function getAverageTime(\DateTime $startDate, \DateTime $endDate): int {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'select' => ['SUM(duration) / COUNT(*) as avgSessionTime'],
			'where' => ["start >= '$startDateStr'", "start <= '$endDateStr'"]
		]);

		return count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;
	}

	public function getSessionsDaily(\DateTime $startDate, \DateTime $endDate) {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'DATE_FORMAT(se.start, \'%Y-%m-%d\') as date',
				'count(*) as sessions'
			],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'"],
			'group' => ['DATE_FORMAT(se.start, \'%Y-%m-%d\')']
		]);


		$output = [];
		foreach ($result as $record) {
			$output[$record->date] = intval($record->sessions);
		}

		$sessions = [];
		$endDate->modify('+1 day');
		while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
			$dateStr = $startDate->format('Y-m-d');

			$sessions[] = [
				'date' => $dateStr,
				'sessions' => isset($output[$dateStr]) ? $output[$dateStr] : 0
			];

			$startDate->modify('+1 day');
		}

		return [
			'sessions' => $sessions
		];
	}

}