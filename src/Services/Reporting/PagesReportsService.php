<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\DAO\EventTypesDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\EventResource;
use Kainex\WiseAnalytics\Services\Commons\DataAccess;

class PagesReportsService {
	use DataAccess;

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

	public function getTotalPageViews(\DateTime $startDate, \DateTime $endDate): int {
		$eventType = $this->eventTypesDAO->getBySlug('page-view');
		if (!$eventType) {
			throw new \Exception('Missing event type');
		}

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents(['select' => ['COUNT(*) AS total'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'", "type_id" => $eventType->getId()]]);

		return count($result) > 0 ? (int) $result[0]->total : 0;
	}

	public function getTopPagesViews(\DateTime $startDate, \DateTime $endDate): array {
		$eventType = $this->eventTypesDAO->getBySlug('page-view');
		if (!$eventType) {
			throw new \Exception('Missing event type');
		}

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => ['count(ev.uri) as pageViews, ev.uri, re.text_value as title'],
			'join' => [[Installer::getEventResourcesTable().' re', ['re.text_key = ev.uri', 're.type_id = '.EventResource::TYPE_URI_TITLE]]],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'", "ev.type_id" => $eventType->getId()],
			'group' => ['ev.uri'],
			'order' => ['pageViews DESC'],
			'limit' => 10
		]);

		return [
			'pages' => $result
		];
	}

}