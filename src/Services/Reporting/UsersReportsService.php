<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Services\Commons\DataAccess;
use Kainex\WiseAnalytics\Utils\TimeUtils;
use Ramsey\Uuid\Type\Time;

class UsersReportsService {
	use DataAccess;

	public function getTotalUsers(\DateTime $startDate, \DateTime $endDate): int {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents(['select' => ['COUNT(DISTINCT user_id) AS users'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'"]]);

		return count($result) > 0 ? (int) $result[0]->users : 0;
	}

	public function getVisitors(\DateTime $startDate, \DateTime $endDate): array {
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

}