<?php

namespace Kainex\WiseAnalytics\Services\Commons;

use Kainex\WiseAnalytics\Installer;

trait DataAccess {

	/**
	 * @param array $definition
	 * @return object[]
	 * @throws \Exception
	 */
	protected function queryEvents(array $definition): array {
		return $this->query(Installer::getEventsTable(), $definition);
	}

	/**
	 * @param array $definition
	 * @return object[]
	 * @throws \Exception
	 */
	protected function querySessions(array $definition): array {
		return $this->query(Installer::getSessionsTable(), $definition);
	}

	/**
	 * @param string $table
	 * @param array $definition
	 * @return object[]
	 * @throws \Exception
	 */
	protected function query(string $table, array $definition): array {
		global $wpdb;

		if (!isset($definition['where'])) {
			throw new \Exception('No "where" conditions specified');
		}

		if (strpos($table, $wpdb->prefix) !== 0) {
			$table = $wpdb->prefix.$table;
		}

		$aliasSQL = isset($definition['alias']) ? ' AS '.$definition['alias'] : '';
		$selectSQL = isset($definition['select']) ? implode(', ', $definition['select']) : '*';
		$whereSQL = implode(' AND ', $definition['where']);
		$groupBySQL = isset($definition['group']) ? 'GROUP BY '.implode(', ', $definition['group']) : '';
		$orderBySQL = isset($definition['order']) ? 'ORDER BY '.implode(', ', $definition['order']) : '';
		$joins = [];
		if (isset($definition['join'])) {
			foreach ($definition['join'] as $join) {
				$joins[] = sprintf('LEFT JOIN %s ON (%s)', $join[0], implode(' AND ', $join[1]));
			}
		}
		$joinsSQL = implode(" ", $joins);
		$limitSQL = isset($definition['limit']) ? ' LIMIT '.$definition['limit'] : '';
		$offsetSQL = isset($definition['offset']) ? ' OFFSET '.$definition['offset'] : '';

		$sql = sprintf(
			"SELECT %s FROM `%s` %s %s WHERE %s %s %s %s %s",
			$selectSQL, $table, $aliasSQL, $joinsSQL, $whereSQL, $groupBySQL, $orderBySQL, $limitSQL, $offsetSQL
		);

		if (isset($definition['outerQuery'])) {
			$sql = sprintf($definition['outerQuery'], $sql);
		}

		$results = $wpdb->get_results($sql);
		if ($wpdb->last_error) {
			throw new \Exception('Data layer error: '.$wpdb->last_error);
		}

		if (is_array($results)) {
			return $results;
		}

		return [];
	}

}