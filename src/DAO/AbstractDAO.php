<?php

namespace Kainex\WiseAnalytics\DAO;

abstract class AbstractDAO {

	abstract protected function getTable(): string;

	/**
	 * @param string $fieldName
	 * @param string $fieldValue
	 *
	 * @return object|null
	 */
	protected function getByField(string $fieldName, string $fieldValue): ?object {
		global $wpdb;

		$sql = sprintf("SELECT * FROM `%s` WHERE `%s` = '%s';", $this->getTable(), $fieldName, addslashes($fieldValue));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $results[0];
		}

		return null;
	}

	/**
	 * @param array $conditions
	 */
	protected function deleteByConditions(array $conditions) {
		global $wpdb;

		$sql = sprintf("DELETE FROM `%s` WHERE %s;", $this->getTable(), implode(' AND ', $conditions));
		$wpdb->get_results($sql);
	}

}