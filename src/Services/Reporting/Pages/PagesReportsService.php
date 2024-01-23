<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Pages;

use Kainex\WiseAnalytics\DAO\EventTypesDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\EventResource;
use Kainex\WiseAnalytics\Model\EventType;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;

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
		$eventType = $this->getPageViewEventType();

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents(['select' => ['COUNT(*) AS total'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'", "type_id" => $eventType->getId()]]);
		$total = count($result) > 0 ? (int) $result[0]->total : 0;

		// compare to the previous period:
		list($startDate, $endDate) = $this->getDatesToCompare($startDate, $endDate);
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$result = $this->queryEvents(['select' => ['COUNT(*) AS total'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'", "type_id" => $eventType->getId()]]);
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
		$eventType = $this->getPageViewEventType();

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$condition = ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'", "ev.type_id" => $eventType->getId()];

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => ['count(ev.uri) as pageViews, ev.uri, re.text_value as title'],
			'join' => [[Installer::getEventResourcesTable().' re', ['re.text_key = ev.uri', 're.type_id = '.EventResource::TYPE_URI_TITLE]]],
			'where' => $condition,
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
			'where' => $condition
		]);

		return [
			'pages' => $result,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];
	}

	public function getPagesViewsDaily(array $queryParams): array {
		list($startDate, $endDate) = $this->getDatesFilters($queryParams);
		$eventType = $this->getPageViewEventType();
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'DATE_FORMAT(ev.created, \'%Y-%m-%d\') as date',
				'count(*) as pageViews'
			],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'", "ev.type_id" => $eventType->getId()],
			'group' => ['DATE_FORMAT(ev.created, \'%Y-%m-%d\')']
		]);


		$output = [];
		foreach ($result as $record) {
			$output[$record->date] = intval($record->pageViews);
		}

		$pageViews = [];
		$endDate->modify('+1 day');
		while ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
			$dateStr = $startDate->format('Y-m-d');

			$pageViews[] = [
				'date' => $dateStr,
				'pageViews' => isset($output[$dateStr]) ? $output[$dateStr] : 0
			];

			$startDate->modify('+1 day');
		}

		return [
			'pageViews' => $pageViews
		];
	}

	/**
	 * @return EventType
	 * @throws \Exception
	 */
	private function getPageViewEventType(): EventType 	{
		$eventType = $this->eventTypesDAO->getBySlug('page-view');
		if (!$eventType) {
			throw new \Exception('Missing event type');
		}

		return $eventType;
	}

}