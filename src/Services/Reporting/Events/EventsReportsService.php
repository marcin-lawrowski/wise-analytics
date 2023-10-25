<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Events;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class EventsReportsService extends ReportingService {

	public function getEvents(\DateTime $startDate, \DateTime $endDate): array {
		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		$result = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'ev.uri',
				'ev.created',
				'ev.user_id as visitorId',
				'us.first_name as visitorFirstName',
				'us.last_name as visitorLastName',
				'ev.type_id as typeId',
				'et.name as typeName'
			],
			'join' => [
				[Installer::getEventTypesTable().' et', ['ev.type_id = et.id']],
				[Installer::getUsersTable().' us', ['ev.user_id = us.id']]
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