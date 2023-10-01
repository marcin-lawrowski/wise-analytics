<?php

namespace Kainex\WiseAnalytics\Services\Commons;

use Kainex\WiseAnalytics\Installer;

trait DataAccess {

	/**
	 * @param string[] $selects
	 * @param string[] $conditions
	 * @return object[]
	 */
	protected function queryEvents(array $selects, array $conditions): array {
		return $this->query(Installer::getEventsTable(), $selects, $conditions);
	}

	/**
	 * @param string[] $selects
	 * @param string[] $conditions
	 * @return object[]
	 */
	protected function querySessions(array $selects, array $conditions): array {
		return $this->query(Installer::getSessionsTable(), $selects, $conditions);
	}

	/**
	 * @param string $table
	 * @param string[] $selects
	 * @param string[] $conditions
	 * @return object[]
	 */
	private function query(string $table, array $selects, array $conditions): array {
		global $wpdb;

		$sql = sprintf(
			"SELECT %s FROM `%s` WHERE %s;",
			implode(', ', $selects), $table, implode(' AND ', $conditions)
		);
		$results = $wpdb->get_results($sql);
		if (is_array($results)) {
			return $results;
		}

		return [];
	}

}