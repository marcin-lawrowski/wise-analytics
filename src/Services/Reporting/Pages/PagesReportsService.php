<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Pages;

use Kainex\WiseAnalytics\DAO\EventTypesDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\EventResource;
use Kainex\WiseAnalytics\Model\EventType;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class PagesReportsService extends ReportingService {

	/** @var EventTypesDAO */
	private $eventTypesDAO;

	/**
	 * PagesReportsService constructor.
	 * @param EventTypesDAO $eventTypesDAO
	 */
	public function __construct(EventTypesDAO $eventTypesDAO)
	{
		$this->eventTypesDAO = $eventTypesDAO;
	}

	public function getTotalPageViews(\DateTime $startDate, \DateTime $endDate): array {
		$eventType = $this->getEventType('page-view');

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'select' => ['COUNT(*) AS total'],
			'where' => ["created >= %s", "created <= %s", "type_id = %d"],
			'whereArgs' => [$startDateStr, $endDateStr, $eventType->getId()]
		]);
		$total = count($result) > 0 ? (int) $result[0]->total : 0;

		// compare to the previous period:
		list($startDate, $endDate) = $this->getDatesToCompare($startDate, $endDate);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$result = $this->queryEvents([
			'select' => ['COUNT(*) AS total'],
			'where' => ["created >= %s", "created <= %s", "type_id = %d"],
			'whereArgs' => [$startDateStr, $endDateStr, $eventType->getId()]
		]);
		$previousTotal = count($result) > 0 ? (int) $result[0]->total : 0;

		return [
			'total' => $total,
			'previousTotal' => $previousTotal,
			'totalDiffPercent' => $previousTotal > 0
				? round((($total - $previousTotal) / $previousTotal * 100), 2)
				: null
		];
	}

	public function getTopPagesViews(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$offset = intval($queryParams['offset'] ?? 0);
		$eventType = $this->getEventType('page-view');

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$condition = ["ev.created >= %s", "ev.created <= %s", "ev.type_id = %d"];
		$conditionArgs = [$startDateStr, $endDateStr, $eventType->getId()];

		$results = $this->queryEvents([
			'alias' => 'ev',
			'select' => ['count(ev.uri) as pageViews, ev.uri, re.text_value as title'],
			'join' => [[Installer::getEventResourcesTable(), 're', ['re.text_key = ev.uri', 're.type_id = '.EventResource::TYPE_URI_TITLE]]],
			'where' => $condition,
			'whereArgs' => $conditionArgs,
			'group' => ['ev.uri'],
			'order' => ['pageViews DESC'],
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		]);

		$count = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.id) as total'
			],
			'group' => ['ev.uri'],
			'where' => $condition,
			'whereArgs' => $conditionArgs
		]);

		return [
			'pages' => $results,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];
	}

	public function getPages(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$filters = $queryParams['filters'];
		$offset = intval($queryParams['offset'] ?? 0);
		$sortColumn = $queryParams['sortColumn'] ?? 'pageViews';
		$sortDirection = $queryParams['sortDirection'] ?? 'desc';
		$scope = $filters['scope'] ?? 'all';
		$eventType = $this->getEventType('page-view');

		if (!in_array($sortColumn, ['pageViews', 'uniquePageViews', 'title', 'avgDuration', 'lastViewed', 'firstViewed'])) {
			throw new \Exception(esc_textarea("Invalid sort column '$sortColumn'"));
		}
		if (!in_array($sortDirection, ['asc', 'desc'])) {
			throw new \Exception(esc_textarea("Invalid sort direction '$sortDirection'"));
		}

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$condition = ["ev.created >= %s", "ev.created <= %s", "ev.type_id = %d"];
		$conditionArgs = [$startDateStr, $endDateStr, $eventType->getId()];
		$dataJoins =  [
			[Installer::getEventResourcesTable(), 're', ['re.text_key = ev.uri', 're.type_id = '.EventResource::TYPE_URI_TITLE]],
		];
		$countJoins = [];

		// scope filters:
		if (in_array($scope, ['entry', 'exit'])) {
			$condition[] = 'se.id IS NOT NULL';
		}
		if ($scope == 'entry') {
			$dataJoins[] = [Installer::getSessionsTable(), 'se', ['se.first_event = ev.id']];
			$countJoins[] = [Installer::getSessionsTable(), 'se', ['se.first_event = ev.id']];
		}
		if ($scope == 'exit') {
			$dataJoins[] = [Installer::getSessionsTable(), 'se', ['se.last_event = ev.id']];
			$countJoins[] = [Installer::getSessionsTable(), 'se', ['se.last_event = ev.id']];
		}

		$results = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.uri) as pageViews',
				'count(distinct ev.user_id) as uniquePageViews',
				'ev.uri',
				're.text_value as title',
				'sum(ev.duration) / count(ev.uri) as avgDuration',
				'max(ev.created) as lastViewed',
				'min(ev.created) as firstViewed'
			],
			'join' => $dataJoins,
			'where' => $condition,
			'whereArgs' => $conditionArgs,
			'group' => ['ev.uri'],
			'order' => [$sortColumn.' '.$sortDirection],
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		]);

		$results = $this->formatResults($results, [
			'avgDuration' => 'duration',
			'lastViewed' => 'timestamp',
			'firstViewed' => 'timestamp',
		]);

		$count = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.id) as total'
			],
			'join' => $countJoins,
			'group' => ['ev.uri'],
			'where' => $condition,
			'whereArgs' => $conditionArgs
		]);

		return [
			'pages' => $results,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset,
			'sortColumn' => $sortColumn,
			'sortDirection' => $sortDirection
		];
	}

	public function getExternalPages(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$filters = $queryParams['filters'];
		$offset = intval($queryParams['offset'] ?? 0);
		$sortColumn = $queryParams['sortColumn'] ?? 'pageViews';
		$sortDirection = $queryParams['sortDirection'] ?? 'desc';
		$eventType = $this->getEventType('external-page-view');

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$condition = ["ev.created >= %s", "ev.created <= %s", "ev.type_id = %d"];
		$conditionArgs = [$startDateStr, $endDateStr, $eventType->getId()];

		if (!in_array($sortColumn, ['pageViews', 'uniquePageViews', 'uri', 'lastViewed', 'firstViewed'])) {
			throw new \Exception(esc_textarea("Invalid sort column '$sortColumn'"));
		}
		if (!in_array($sortDirection, ['asc', 'desc'])) {
			throw new \Exception(esc_textarea("Invalid sort direction '$sortDirection'"));
		}

		$results = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.uri) as pageViews',
				'count(distinct ev.user_id) as uniquePageViews',
				'ev.uri',
				'max(ev.created) as lastViewed',
				'min(ev.created) as firstViewed'
			],
			'where' => $condition,
			'whereArgs' => $conditionArgs,
			'group' => ['ev.uri'],
			'order' => [$sortColumn.' '.$sortDirection],
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		]);

		$results = $this->formatResults($results, [
			'lastViewed' => 'timestamp',
			'firstViewed' => 'timestamp',
		]);

		$count = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.id) as total'
			],
			'group' => ['ev.uri'],
			'where' => $condition,
			'whereArgs' => $conditionArgs
		]);

		return [
			'pages' => $results,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset,
			'sortColumn' => $sortColumn,
			'sortDirection' => $sortDirection
		];
	}

	public function getPagesViews(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$eventType = $this->getEventType('page-view');
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$period = $this->getModifier($queryParams, 'period', 'daily');
		$groupExpression = $this->getGroupingExpressionByPeriod($period, 'ev.created');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				$groupExpression.' as date',
				'count(*) as pageViews'
			],
			'where' => ["ev.created >= %s", "ev.created <= %s", "ev.type_id = %d"],
			'whereArgs' => [$startDateStr, $endDateStr, $eventType->getId()],
			'group' => [$groupExpression]
		]);

		$result = $this->formatResults($result, [
			'pageViews' => 'integer'
		]);

		return [
			'pageViews' => $this->fillResultsWithZeroValues($result, $period, $startDate, $endDate, ['pageViews' => 0])
		];
	}

	private function getEventType(string $slug): EventType 	{
		$eventType = $this->eventTypesDAO->getBySlug($slug);
		if (!$eventType) {
			throw new \Exception('Missing event type');
		}

		return $eventType;
	}

}