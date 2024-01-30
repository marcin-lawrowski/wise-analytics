<?php

namespace Kainex\WiseAnalytics\Services\Processing;

use Kainex\WiseAnalytics\DAO\Stats\SessionsDAO;
use Kainex\WiseAnalytics\Model\Stats\Session;
use Kainex\WiseAnalytics\Services\Commons\DataAccess;
use Kainex\WiseAnalytics\Utils\Logger;

class SessionsService {
	use DataAccess;
	
	const SESSION_GAP = 1800;

	/** @var int Average duration of the last event in a session */
	const EXTRA_SECONDS = 5;

	/** @var SessionsDAO */
	private $sessionsDAO;

	/** @var Logger */
	private $logger;

	/**
	 * SessionsService constructor.
	 * @param SessionsDAO $sessionsDAO
	 * @param Logger $logger
	 */
	public function __construct(SessionsDAO $sessionsDAO, Logger $logger)
	{
		$this->sessionsDAO = $sessionsDAO;
		$this->logger = $logger;
	}

	/**
	 * Refreshes sessions of a given day.
	 * TODO: safety limit of the sessions or offset
	 *
	 * @param \DateTime $day The day to refresh the sessions
	 * @throws \Exception
	 */
	public function refresh(\DateTime $day) {
		$startDate = clone $day;
		$startDate->setTime(0, 0, 0);
		$endDate = clone $day;
		$endDate->setTime(23, 59, 59);

		$startDateStr = $startDate->format('Y-m-d H:i:s');
		$endDateStr = $endDate->format('Y-m-d H:i:s');

		try {

			$this->logger->info('Refreshing sessions, time range: ' . $startDateStr . ' - ' . $endDateStr);

			$users = $this->queryEvents(['select' => ['DISTINCT user_id'], 'where' => ["created >= '$startDateStr'", "created <= '$endDateStr'"]]);
			$totalEvents = 0;
			foreach ($users as $user) {
				$totalEvents += $this->refreshUserSessions($user->user_id, $startDateStr, $endDateStr);
			}

			$this->logger->info('Refreshed users: ' . count($users) . ', events processed: ' . $totalEvents);
		} catch (\Exception $exception) {
			$this->logger->error('Error when refreshing sessions: ' . $exception->getMessage());
		}
	}

	private function refreshUserSessions(int $userId, string $startDate, string $endDate): int {
		$this->sessionsDAO->deleteByUserAndDate($userId, $startDate, $endDate);

		$events = $this->queryEvents(['select' => ['*'], 'where' => ["user_id = $userId", "created >= '$startDate'", "created <= '$endDate'"]]);

		$this->createSessions($userId, $this->getGroupedEvents($events));

		return count($events);
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
			$session->setSource($this->getSource($firstEvent));

			$this->sessionsDAO->save($session);
		}
	}

	private function getSource(object $firstEvent): ?string {
		$data = json_decode($firstEvent->data, true);
		if (!is_array($data) || !isset($data['referer']) || !$data['referer']) {
			return null;
		}

		$sourceDomain = parse_url($data['referer'], PHP_URL_HOST);
		if ($sourceDomain && filter_var($sourceDomain, FILTER_VALIDATE_DOMAIN)) {
			return preg_replace('/^www\./', '', $sourceDomain);
		}

		return null;
	}

}