<?php

namespace Kainex\WiseAnalytics\Services\Reporting;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Services\Commons\DataAccess;
use Kainex\WiseAnalytics\Utils\TimeUtils;
use Ramsey\Uuid\Type\Time;

class EventsReportsService {
	use DataAccess;

	public function getEvents(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'ev.uri',
				'ev.created',
				'ev.user_id as visitorId',
				'ev.type_id as typeId'
			],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"],
			'order' => ['created DESC'],
			'limit' => 20
		]);

		$output = [];
		foreach ($result as $record) {
			$record->created = TimeUtils::formatTimestamp($record->created);
			$output[] = $record;
		}

		return [
			'events' => $output
		];
	}

}