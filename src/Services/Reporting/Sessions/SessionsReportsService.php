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

		$sources = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(distinct se.user_id) AS totalVisitors',
				'se.source_category AS source'
			],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'", 'se.source_category IS NOT NULL', "se.source_category <> ''"],
			'group' => ['se.source_category']
		]);

		return [
			'sourceCategories' => $sources
		];
	}

	public function getSourceCategoriesDaily(array $queryParams) {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'DATE_FORMAT(se.start, \'%Y-%m-%d\') as date',
				'se.source_category',
				'count(*) as sessions'
			],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'", 'se.source_category IS NOT NULL', "se.source_category <> ''"],
			'group' => ['DATE_FORMAT(se.start, \'%Y-%m-%d\')', 'se.source_category']
		]);

		$output = [];
		$allCategories = [];
		foreach ($result as $record) {
			$sourceCategory = $record->source_category ?? 'Unknown';
			$output[$record->date][$sourceCategory] = intval($record->sessions);
			$allCategories[] = $sourceCategory;
		}

		$allCategories = array_values(array_unique($allCategories));

		$sourceCategories = [];
		$endDate->modify('+1 day');
		while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
			$dateStr = $startDate->format('Y-m-d');

			$entry = [
				'date' => $dateStr
			];
			foreach ($allCategories as $category) {
				$entry[$category] = isset($output[$dateStr]) && isset($output[$dateStr][$category]) ? $output[$dateStr][$category] : 0;
			}

			$sourceCategories[] = $entry;

			$startDate->modify('+1 day');
		}

		return [
			'sourceCategories' => $sourceCategories
		];
	}

	/**
	 * Queries sessions of particular source category.
	 *
	 * @param array $queryParams
	 * @return array
	 * @throws \Exception
	 */
	public function getSourceCategory(array $queryParams) {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$offset = intval($queryParams['offset'] ?? 0);
		$category = $queryParams['filters']['category'];

		$args = [
			'alias' => 'se',
			'select' => ['SUM(se.duration) / COUNT(*) as avgSessionTime', 'COUNT(*) AS totalSessions', 'JSON_LENGTH(se.events) / COUNT(*) AS eventsPerSession'],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'", "se.source_category = '".addslashes($category)."'"],
			'order' => ["totalSessions DESC"],
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];

		if (in_array($category, ['Social Network', 'Organic'])) {
			$args['select'][] = 'se.source_group AS sourceGroup';
			$args['group'] = ['se.source_group'];
		} else if ($category === 'Referral') {
			$args['select'][] = 'se.source AS sourceGroup';
			$args['group'] = ['se.source'];
		}

		$count = $this->querySessions([
			'alias' => 'se',
			'select' => [
				$args['group'][0]
			],
			'group' => $args['group'],
			'where' => $args['where'],
			'outerQuery' => 'SELECT COUNT(*) AS total FROM (%s) innerSQL'
		]);

		$sources = $this->querySessions($args);
		foreach ($sources as $key => $source) {
			$source->avgSessionTime = $source->avgSessionTime > 0 ? TimeUtils::formatDuration($source->avgSessionTime, 'suffixes') : '0s';
			$source->eventsPerSession = round($source->eventsPerSession, 1);
		}

		return [
			'sources' => $sources,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];
	}

}