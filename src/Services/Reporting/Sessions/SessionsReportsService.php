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
			'where' => ["start >= %s", "start <= %s"],
			'whereArgs' => [$startDateStr, $endDateStr]
		]);

		$avgSessionTime = count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;

		list($startDate, $endDate) = $this->getDatesToCompare($startDate, $endDate);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$result = $this->querySessions([
			'select' => ['SUM(duration) / COUNT(*) as avgSessionTime'],
			'where' => ["start >= %s", "start <= %s"],
			'whereArgs' => [$startDateStr, $endDateStr]
		]);
		$previousAvgSessionTime = count($result) > 0 ? (int) $result[0]->avgSessionTime : 0;

		return [
			'time' => TimeUtils::formatDuration($avgSessionTime, 'suffixes'),
			'previousTime' => TimeUtils::formatDuration($previousAvgSessionTime, 'suffixes'),
			'timeDiffPercent' => $previousAvgSessionTime > 0 ? round((($avgSessionTime - $previousAvgSessionTime) / $previousAvgSessionTime * 100), 2) : null
		];
	}

	public function getSessionsAvgTime($queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$period = $this->getModifier($queryParams, 'period', 'daily');
		$groupExpression = $this->getGroupingExpressionByPeriod($period, 'se.start');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				$groupExpression.' as date',
				'SUM(duration) / COUNT(*) as avgSessionTime',
			],
			'where' => ["se.start >= %s", "se.start <= %s"],
			'whereArgs' => [$startDateStr, $endDateStr],
			'group' => [$groupExpression]
		]);

		$output = [];
		foreach ($result as $record) {
			$avgSessionTime = intval($record->avgSessionTime);
			$output[] = (object) [
				'date' => $record->date,
				'time' => $avgSessionTime,
				'timeFormatted' => TimeUtils::formatDuration($avgSessionTime)
			];
		}

		return [
			'sessions' => $this->fillResultsWithZeroValues($output, $period, $startDate, $endDate, ['time' => 0, 'timeFormatted' => '0s'])
		];
	}

	public function getSessions(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$period = $this->getModifier($queryParams, 'period', 'daily');
		$groupExpression = $this->getGroupingExpressionByPeriod($period, 'se.start');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				$groupExpression.' as date',
				'count(*) as sessions'
			],
			'where' => ["se.start >= %s", "se.start <= %s"],
			'whereArgs' => [$startDateStr, $endDateStr],
			'group' => [$groupExpression]
		]);

		$result = $this->formatResults($result, [
			'sessions' => 'integer'
		]);

/*
		$output = [];
		foreach ($result as $record) {
			$output[$record->date] = intval($record->sessions);
		}
*/

		return [
			'sessions' => $this->fillResultsWithZeroValues($result, $period, $startDate, $endDate, ['sessions' => 0])
		];
	}

	public function getSessionsOfVisitorHourly(array $params): array {
		if (!isset($params['filters']['visitorId'])) {
			throw new \Exception('Missing ID');
		}

		$visitorId = intval($params['filters']['visitorId']);

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'DATE_FORMAT(se.local_time, \'%%H\') as hour',
				'count(*) as totalSessions'
			],
			'where' => ["se.user_id = %d", 'local_time IS NOT NULL'],
			'whereArgs' => [$visitorId],
			'group' => ['DATE_FORMAT(se.local_time, \'%%H\')'],
			'order' => ['hour ASC']
		]);

		$map = [];
		foreach ($result as $hour) {
			$map[intval($hour->hour)] = $hour;
		}

		$output = [];
		for ($i = 0; $i < 24; $i++) {
			$output[] = $map[$i] ?? [
				'hour' => str_pad($i, 2, '0', STR_PAD_LEFT),
				'totalSessions' => 0
			];
		}

		return ['hourly' => $output];
	}

}