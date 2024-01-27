<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Visitors;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Services\Users\VisitorsService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class VisitorsReportsService extends ReportingService {

	public function getVisitorsHighlights(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$output = [];
		$result = $this->queryEvents(['select' => ['COUNT(DISTINCT user_id) AS users'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'"]]);
		$output['total'] = $result ? (int) $result[0]->users : 0;

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => ['COUNT(DISTINCT ev.user_id) AS newUsers'],
			'join' => [[Installer::getUsersTable().' us', ['ev.user_id = us.id']]],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'", "us.created >= '$startDateStr'"]
		]);
		$output['new'] = $result ? (int) $result[0]->newUsers : 0;
		$output['returning'] = $output['total'] - $output['new'];
		$output['percentReturning'] = $output['total'] ? round($output['returning'] / $output['total'] * 100, 2) : 0;
		$output['percentNew'] = $output['total'] ? round($output['new'] / $output['total'] * 100, 2) : 0;

		// compare to the previous period:
		list($startDate, $endDate) = $this->getDatesToCompare($startDate, $endDate);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$result = $this->queryEvents(['select' => ['COUNT(DISTINCT user_id) AS users'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'"]]);
		$previousTotal = $result ? (int) $result[0]->users : 0;
		$output['previousTotal'] = $previousTotal;
		$output['totalDiffPercent'] = $previousTotal > 0
			? round((($output['total'] - $previousTotal) / $previousTotal * 100), 2)
			: null;

		return $output;
	}

	public function getLastVisitors(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$offset = intval($queryParams['offset'] ?? 0);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(se.id) as totalSessions',
				'sum(se.duration) / count(se.id) as avgSessionDuration',
				'se.user_id as id',
				'sum(JSON_LENGTH(JSON_EXTRACT(se.events, "$"))) as totalEvents',
				'max(start) as lastVisit',
				'us.first_name as firstName',
				'us.last_name as lastName',
			],
			'join' => [[Installer::getUsersTable().' us', ['se.user_id = us.id']]],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'", "us.id IS NOT NULL"],
			'group' => ['se.user_id'],
			'order' => ['lastVisit DESC'],
			'offset' => $offset,
			'limit' => self::RESULTS_LIMIT
		]);

		$output = [];
		foreach ($result as $record) {
			$avgSessionDuration = intval($record->avgSessionDuration);
			$record->avgSessionDuration = $avgSessionDuration > 0 ? TimeUtils::formatDuration($avgSessionDuration, 'suffixes') : '0s';
			$record->lastVisit = TimeUtils::formatTimestamp($record->lastVisit);
			$output[] = $record;
		}

		$count = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(distinct us.id) as total'
			],
			'join' => [[Installer::getUsersTable().' us', ['se.user_id = us.id']]],
			'where' => ["se.start >= '$startDateStr'", "se.start <= '$endDateStr'", "us.id IS NOT NULL"],
		]);

		return [
			'visitors' => $output,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];
	}

	public function getVisitorsDaily(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
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

	public function getLanguages(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		return [
			'languages' => $this->queryEvents([
				'alias' => 'ev',
				'select' => [
					'count(distinct ev.user_id) as totalVisitors',
					'us.language'
				],
				'join' => [[Installer::getUsersTable().' us', ['ev.user_id = us.id']]],
				'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"],
				'group' => ['us.language']
			])
		];
	}

	public function getDevices(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$devicesMap = [
			VisitorsService::DEVICE_DESKTOP => 'Desktop',
			VisitorsService::DEVICE_TABLET => 'Table',
			VisitorsService::DEVICE_MOBILE => 'Mobile',
		];
		$devicesOut = [];
		$devices = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(distinct ev.user_id) as totalVisitors',
				'us.device'
			],
			'join' => [[Installer::getUsersTable().' us', ['ev.user_id = us.id']]],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"],
			'group' => ['us.device']
		]);
		foreach ($devices as $deviceEntry) {
			$devicesOut[] = [
				'device' => isset($devicesMap[$deviceEntry->device]) ? $devicesMap[$deviceEntry->device] : '(not set)',
				'totalVisitors' => $deviceEntry->totalVisitors
			];
		}

		return [
			'devices' => $devicesOut
		];
	}

	public function getInformation(array $params) {
		if (!isset($params['filters']['id'])) {
			throw new \Exception('Missing ID');
		}

		$id = intval($params['filters']['id']);
		$visitor = $this->query(Installer::getUsersTable(), [
			'select' => ['*'],
			'where' => ["id = {$id}"],
		]);

		if (!$visitor) {
			throw new \Exception('Visitor not found');
		}
		$visitor = $visitor[0];

		$sessions = $this->querySessions([
			'alias' => 'se',
			'select' => [
				'count(se.id) as totalSessions',
				'sum(se.duration) / count(se.id) as avgSessionDuration',
				'sum(JSON_LENGTH(JSON_EXTRACT(se.events, "$"))) as totalEvents',
				'max(start) as lastVisit'
			],
			'where' => ["se.user_id = {$id}"]
		]);
		$sessions = $sessions[0];
		$avgSessionDuration = intval($sessions->avgSessionDuration);

		return [
			'id' => $visitor->id,
			'name' => trim($visitor->first_name.' '.$visitor->last_name),
			'email' => $visitor->email,
			'company' => $visitor->company,
			'language' => $visitor->language,
			'screenWidth' => $visitor->screen_width,
			'screenHeight' => $visitor->screen_height,
			'firstVisit' => $visitor->created,
			'lastVisit' => TimeUtils::formatTimestamp($sessions->lastVisit),
			'data' => json_decode($visitor->data),
			'totalSessions' => intval($sessions->totalSessions),
			'totalEvents' => intval($sessions->totalEvents),
			'avgSessionDuration' => $avgSessionDuration > 0 ? TimeUtils::formatDuration($avgSessionDuration, 'suffixes') : '0s',
		];
	}

}