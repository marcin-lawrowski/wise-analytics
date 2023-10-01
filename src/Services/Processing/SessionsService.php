<?php

namespace Kainex\WiseAnalytics\Services\Processing;

use Kainex\WiseAnalytics\DAO\Stats\SessionsDAO;
use Kainex\WiseAnalytics\Model\Stats\Session;
use Kainex\WiseAnalytics\Services\Commons\DataAccess;

class SessionsService {
	use DataAccess;
	
	const SESSION_GAP = 1800;

	/** @var int Average duration of the last event in a session */
	const EXTRA_SECONDS = 5;

	/** @var SessionsDAO */
	private $sessionsDAO;

	/**
	 * SessionsService constructor.
	 * @param SessionsDAO $sessionsDAO
	 */
	public function __construct(SessionsDAO $sessionsDAO)
	{
		$this->sessionsDAO = $sessionsDAO;
	}

	/**
	 * Refreshes sessions of a given day.
	 * TODO: safety limit of the sessions or offset
	 *
	 * @param \DateTime $day The day to refresh the sessions
	 */
	public function refresh(\DateTime $day) {
		$startDate = clone $day;
		$startDate->setTime(0, 0, 0);
		$endDate = clone $day;
		$endDate->setTime(23, 59, 59);

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');
		$users = $this->queryEvents(['DISTINCT user_id'], ["created >= '$startDateStr'", "created <= '$endDateStr'"]);

		foreach ($users as $user) {
			$this->refreshUserSessions($user->user_id, $startDateStr, $endDateStr);
		}
	}

	private function refreshUserSessions(int $userId, string $startDate, string $endDate) {
		$this->sessionsDAO->deleteByUserAndDate($userId, $startDate, $endDate);

		$events = $this->queryEvents(['*'], ["user_id" => $userId, "created >= '$startDate'", "created <= '$endDate'"]);
		$this->createSessions($userId, $this->getGroupedEvents($events));
	}

	/**
	 * Group events into sessions.
	 *
	 * @param array $events
	 * @return array
	 */
	private function getGroupedEvents(array $events): array {
		if (count($events) === 0) {
			return [];
		}

		$sessions = [];
		$sessionEvents = [];
		$previousEvent = null;
		foreach ($events as $event) {
			if ($previousEvent && (strtotime($event->created) - strtotime($previousEvent->created)) > self::SESSION_GAP) {
				$sessions[] = $sessionEvents;
				$sessionEvents = [];
			}
			$sessionEvents[] = $event;
			$previousEvent = $event;
		}

		$sessions[] = $sessionEvents;

		return $sessions;
	}

	/**
	 * Creates user sessions based on grouped events.
	 *
	 * @param int $userId
	 * @param array $groupedEvents
	 * @throws \Exception
	 */
	private function createSessions(int $userId, array $groupedEvents) {
		foreach ($groupedEvents as $group) {
			$ids = array_map(function($event) { return (int) $event->id; }, $group);

			$firstEvent = $group[0];
			$lastEvent = count($group) > 1 ? $group[count($group) - 1] : $group[0];

			$duration = strtotime($lastEvent->created) - strtotime($firstEvent->created) + self::EXTRA_SECONDS;

			$startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $firstEvent->created);
			$endDate = \DateTime::createFromFormat('Y-m-d H:i:s', $lastEvent->created);
			$endDate->modify("+".self::EXTRA_SECONDS." seconds");

			$session = new Session();
			$session->setUserId($userId);
			$session->setEvents($ids);
			$session->setDuration($duration);
			$session->setStart($startDate);
			$session->setEnd($endDate);

			$this->sessionsDAO->save($session);
		}
	}

}