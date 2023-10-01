<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\DAO\EventTypesDAO;
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

		$result = $this->queryEvents(['COUNT(*) AS total'], ["created >= '$startDateStr'", "created <= '$endDateStr'", "type_id" => $eventType->getId()]);

		return count($result) > 0 ? (int) $result[0]->total : 0;
	}

}