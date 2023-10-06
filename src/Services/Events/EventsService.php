<?php

namespace Kainex\WiseAnalytics\Services\Events;

use Kainex\WiseAnalytics\DAO\EventsDAO;
use Kainex\WiseAnalytics\DAO\EventTypesDAO;
use Kainex\WiseAnalytics\Model\Event;
use Kainex\WiseAnalytics\Model\EventType;
use Kainex\WiseAnalytics\Model\User;
use Kainex\WiseAnalytics\Utils\URLUtils;

class EventsService {

	const STANDARD_TYPES = [
		'page-view' => 'Page View'
	];

	/** @var URLUtils */
	private $urlUtils;

	/** @var EventsDAO */
	private $eventsDAO;

	/** @var EventTypesDAO */
	private $eventTypesDAO;

	/**
	 * EventsService constructor.
	 * @param URLUtils $urlUtils
	 * @param EventsDAO $eventsDAO
	 * @param EventTypesDAO $eventTypesDAO
	 */
	public function __construct(URLUtils $urlUtils, EventsDAO $eventsDAO, EventTypesDAO $eventTypesDAO)
	{
		$this->urlUtils = $urlUtils;
		$this->eventsDAO = $eventsDAO;
		$this->eventTypesDAO = $eventTypesDAO;
	}

	/**
	 * @param User $user
	 * @param string $typeSlug
	 * @param string $checksum
	 * @param array $data
	 * @return Event
	 */
	public function createEvent(User $user, string $typeSlug, string $checksum, array $data): Event {
		if (!$checksum) {
			throw new \Exception('Missing checksum');
		}
		if ($this->eventsDAO->getByChecksum($checksum)) {
			throw new \Exception('Invalid checksum');
		}

		$eventType = $this->getOrCreateEventType($typeSlug);

		$event = new Event();
		$event->setUri($this->convertUri($typeSlug, $data));
		$event->setCreated(new \DateTime());
		$event->setUserId($user->getId());
		$event->setTypeId($eventType->getId());
		$event->setChecksum($checksum);
		$event->setData($this->convertData($typeSlug, $data));

		$this->eventsDAO->save($event);

		return $event;
	}

	private function getOrCreateEventType(string $typeSlug): EventType {
		$eventType = $this->eventTypesDAO->getBySlug($typeSlug);
		if (!$eventType) {
			if (isset(self::STANDARD_TYPES[$typeSlug])) {
				$eventType = new EventType();
				$eventType->setName(self::STANDARD_TYPES[$typeSlug]);
				$eventType->setSlug($typeSlug);
				$eventType = $this->eventTypesDAO->save($eventType);
			} else {
				throw new \Exception('Unsupported type of event');
			}
		}

		return $eventType;
	}

	private function convertData(string $typeSlug, array $data): array {
		$output = [];

		switch ($typeSlug) {
			case 'page-view':
				$output = array_filter([
					'ip' => $data['ip'],
					'uri' => $data['uri'],
					'referer' => $data['referer']
				]);
				break;
		}

		return $output;
	}


	private function convertUri(string $typeSlug, array $data): ?string {
		$output = null;

		switch ($typeSlug) {
			case 'page-view':
				if (isset($data['canonicalUri']) && $data['canonicalUri']) {
					$output = $this->urlUtils->toPathAndQuery($data['canonicalUri']);
				} else if (isset($data['uri']) && $data['uri']) {
					$output = $this->urlUtils->toPathAndQuery($data['uri']);
				} else {
					throw new \Exception('Missing URI');
				}

				break;
		}

		return $output;
	}

}