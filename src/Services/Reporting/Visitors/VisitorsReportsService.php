<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Visitors;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class VisitorsReportsService extends ReportingService {

	public function getTotalUsers(\DateTime $startDate, \DateTime $endDate): int {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents(['select' => ['COUNT(DISTINCT user_id) AS users'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'"]]);

		return count($result) > 0 ? (int) $result[0]->users : 0;
	}

	public function getLastVisitors(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(se.id) as totalSessions',
				'sum(se.duration) / count(se.id) as avgSessionDuration',
				'se.user_id as id',
				'sum(JSON_LENGTH(JSON_EXTRACT(se.events, "$"))) as totalEvents',
				'max(start) as lastVisit'
			],
			'join' => [[Installer::getUsersTable().' us', ['se.user_id = us.id']]],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'"],
			'group' => ['se.user_id'],
			'order' => ['lastVisit DESC'],
			'limit' => 20
		]);

		$output = [];
		foreach ($result as $record) {
			$avgSessionDuration = intval($record->avgSessionDuration);
			$record->avgSessionDuration = $avgSessionDuration > 0 ? TimeUtils::formatDuration($avgSessionDuration, 'suffixes') : '0s';
			$record->lastVisit = TimeUtils::formatTimestamp($record->lastVisit);
			$output[] = $record;
		}

		return [
			'visitors' => $output
		];
	}

	public function getVisitorsDaily(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'DATE_FORMAT(ev.created, \'%Y-%m-%d\') as date',
				'count(distinct ev.user_id) as visitors'
			],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"],
			'group' => ['DATE_FORMAT(ev.created, \'%Y-%m-%d\')']
		]);


		$output = [];
		foreach ($result as $record) {
			$output[$record->date] = intval($record->visitors);
		}

		$visitors = [];
		$endDate->modify('+1 day');
		while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
			$dateStr = $startDate->format('Y-m-d');

			$visitors[] = [
				'date' => $dateStr,
				'visitors' => isset($output[$dateStr]) ? $output[$dateStr] : 0
			];

			$startDate->modify('+1 day');
		}

		return [
			'visitors' => $visitors
		];
	}

}