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
		global $wpdb;

		$sql = sprintf(
			"SELECT %s FROM `%s` WHERE %s;",
			implode(', ', $selects), Installer::getEventsTable(), implode(' AND ', $conditions)
		);
		$results = $wpdb->get_results($sql);
		if (is_array($results)) {
			return $results;
		}

		return [];
	}

}