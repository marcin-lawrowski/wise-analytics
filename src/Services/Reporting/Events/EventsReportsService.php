<?php

namespace Kainex\WiseAnalytics\Services\Reporting\Events;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\EventResource;
use Kainex\WiseAnalytics\Services\Reporting\ReportingService;
use Kainex\WiseAnalytics\Utils\TimeUtils;

class EventsReportsService extends ReportingService {

	public function getEvents(\DateTime $startDate, \DateTime $endDate, int $offset): array {
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
				'et.name as typeName',
				're.text_value as title'
			],
			'join' => [
				[Installer::getEventTypesTable().' et', ['ev.type_id = et.id']],
				[Installer::getUsersTable().' us', ['ev.user_id = us.id']],
				[Installer::getEventResourcesTable().' re', ['re.text_key = ev.uri', 're.type_id = '.EventResource::TYPE_URI_TITLE]]
			],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"],
			'order' => ['created DESC'],
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		]);

		$output = [];
		foreach ($result as $record) {
			$record->created = TimeUtils::formatTimestamp($record->created);
			$output[] = $record;
		}

		$count = $this->queryEvents([
			'alias' => 'ev',
			'select' => [
				'count(ev.id) as total'
			],
			'where' => ["ev.created >= '$startDateStr'", "ev.created <= '$endDateStr'"]
		]);

		return [
			'events' => $output,
			'total' => $count ? (int) $count[0]->total : 0,
			'limit' => self::RESULTS_LIMIT,
			'offset' => $offset
		];
	}

}