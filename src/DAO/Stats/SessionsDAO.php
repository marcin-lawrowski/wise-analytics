<?php

namespace Kainex\WiseAnalytics\DAO\Stats;

use Kainex\WiseAnalytics\DAO\AbstractDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\Stats\Session;
use Kainex\WiseAnalytics\Options;

/**
 * SessionsDAO.
 *
 * @author Kainex <contact@kaine.pl>
 */
class SessionsDAO extends AbstractDAO {
	
	/** @var Options */
	private $options;

	protected function getTable(): string {
		return Installer::getSessionsTable();
	}

	public function __construct(Options $options) {
		$this->options = $options;
	}

	/**
	 * @param integer $id
	 *
	 * @return Session|null
	 */
	public function get(int $id): ?Session {
		return $this->populateData($this->getByField('id', (string) $id));
	}

	/**
	 * @param Session $session
	 *
	 * @return Session
	 * @throws \Exception On validation error
	 */
	public function save(Session $session): Session {
		global $wpdb;

		$columns = array(
			'user_id' => $session->getUserId(),
			'duration' => $session->getDuration(),
			'source' => $session->getSource(),
			'events' => json_encode($session->getEvents()),
			'start' => $session->getStart()->format('Y-m-d H:i:s'),
			'end' => $session->getEnd()->format('Y-m-d H:i:s')
		);

		if ($session->getId() !== null) {
			$wpdb->update($this->getTable(), $columns, array('id' => $session->getId()), '%s', '%d');
		} else {
			$wpdb->insert($this->getTable(), $columns);
			$session->setId($wpdb->insert_id);
		}

		return $session;
	}

	/**
	 * @param object $rawData
	 *
	 * @return Session
	 */
	private function populateData(?object $rawData): ?Session {
		if (!$rawData) {
			return null;
		}

		$session = new Session();
		$session->setId(intval($rawData->id));
		$session->setUserId(intval($rawData->user_id));
		$session->setEvents(json_decode($rawData->events, true));
		$session->setDuration($rawData->duration);
		$session->setSource($rawData->source);
		$session->setStart(\DateTime::createFromFormat('Y-m-d H:i:s', $rawData->start));
		$session->setEnd(\DateTime::createFromFormat('Y-m-d H:i:s', $rawData->end));

		return $session;
	}

	public function deleteByUserAndDate(int $userId, string $startDateStr, string $endDateStr) {
		$this->deleteByConditions([
			'user_id = '.$userId,
			"start >= '$startDateStr'",
			"start <= '$endDateStr'"
		]);
	}

}