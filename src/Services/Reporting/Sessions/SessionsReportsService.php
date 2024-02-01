<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Sessions;

use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class SessionsReportsService extends ReportingService {

	public function getAverageTime(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'select' => ['SUM(duration) / COUNT(*) as avgSessionTime'],
			'where' => ["start >= '$startDateStr'", "start <= '$endDateStr'"]
		]);

		$avgSessionTime = count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;

		list($startDate, $endDate) = $this->getDatesToCompare($startDate, $endDate);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$result = $this->querySessions([
			'select' => ['SUM(duration) / COUNT(*) as avgSessionTime'],
			'where' => ["start >= '$startDateStr'", "start <= '$endDateStr'"]
		]);
		$previousAvgSessionTime = count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;

		return [
			'time' => TimeUtils::formatDuration($avgSessionTime, 'suffixes'),
			'previousTime' => TimeUtils::formatDuration($previousAvgSessionTime, 'suffixes'),
			'timeDiffPercent' => $previousAvgSessionTime > 0 ? round((($avgSessionTime - $previousAvgSessionTime) / $previousAvgSessionTime * 100), 2) : null
		];
	}

	public function getSessionsDaily(array $queryParams) {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
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

	public function getSources(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$sourcesOut = [];
		$sources = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(distinct se.user_id) as totalVisitors',
				'se.source'
			],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'"],
			'group' => ['se.source']
		]);
		foreach ($sources as $sourceEntry) {
			$sourcesOut[] = [
				'source' => $sourceEntry->source ? $sourceEntry->source : 'Direct',
				'totalVisitors' => intval($sourceEntry->totalVisitors)
			];
		}

		return [
			'sources' => $sourcesOut
		];
	}

	public function getSourceCategories(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$sourcesOut = [];
		$sources = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(distinct se.user_id) as totalVisitors',
				'se.source_category'
			],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'"],
			'group' => ['se.source_category']
		]);
		foreach ($sources as $sourceEntry) {
			$sourcesOut[] = [
				'source' => $sourceEntry->source_category ? $sourceEntry->source_category : 'Unknown',
				'totalVisitors' => intval($sourceEntry->totalVisitors)
			];
		}

		return [
			'sourceCategories' => $sourcesOut
		];
	}

}