<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Sessions;

use Kainex\WiseAnalytics\Installer;
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

	public function getSessionsOfByNumber(array $queryParams): array {
		global $wpdb;
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$table = Installer::getSessionsTable();
		$sql = "SELECT userTotalVisits, count(userTotalVisits) as userTotalVisitsNumber, sum(userTotalVisitsDuration) as userTotalVisitsDuration
    		FROM (
				SELECT count(se.user_id) as userTotalVisits, sum(se.duration) as userTotalVisitsDuration
				FROM $table se 
				WHERE se.start >= '$startDateStr' AND se.start <= '$endDateStr'
				GROUP BY se.user_id
			) AS inn
			GROUP BY inn.userTotalVisits
    		ORDER BY inn.userTotalVisits
		";

		$results = $wpdb->get_results($sql);
		if ($wpdb->last_error) {
			throw new \Exception('Data layer error: '.$wpdb->last_error);
		}

		$output = [];
		$groupsDefs = [[11, 25], [26, 50], [51, 100], [101, 200], [201, 500]];
		foreach ($results as $result) {
			$userTotalVisits = $result->userTotalVisits;
			$userTotalVisitsNumber = $result->userTotalVisitsNumber;
			$userTotalVisitsDuration = $result->userTotalVisitsDuration;


			if ($userTotalVisits <= 10) {
				$output[$userTotalVisits] = $result;
			}
			if ($userTotalVisits >= 501) {
				$result->userTotalVisits = '501+';
				if (isset($output['501+'])) {
					$output['501+']->userTotalVisitsNumber += $userTotalVisitsNumber;
					$output['501+']->userTotalVisitsDuration += $userTotalVisitsDuration;
				} else {
					$output['501+'] = $result;
				}
			}

			foreach ($groupsDefs as $groupDef) {
				if ($userTotalVisits >= $groupDef[0] && $userTotalVisits <= $groupDef[1]) {
					$key = $groupDef[0].' - '.$groupDef[1];
					if (isset($output[$key])) {
						$output[$key]->userTotalVisitsNumber += $userTotalVisitsNumber;
						$output[$key]->userTotalVisitsDuration += $userTotalVisitsDuration;
					} else {
						$result->userTotalVisits = $key;
						$output[$key] = $result;
					}
				}
			}
		}

		$total = 0;
		foreach ($output as $value) {
			$total += $value->userTotalVisitsNumber;
		}

		foreach ($output as $value) {
			$avgSessionTime = $value->userTotalVisitsNumber > 0 ? $value->userTotalVisitsDuration / $value->userTotalVisitsNumber : 0;
			$value->avgSessionTime = TimeUtils::formatDuration($avgSessionTime, 'suffixes');
			$value->percentageOfTotal = $total > 0 ? round(($value->userTotalVisitsNumber / $total * 100), 2) : null;
		}

		return ['visits' => array_values($output)];
	}

}