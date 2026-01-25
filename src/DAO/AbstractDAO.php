<?php

namespace Kainex\WiseAnalytics\DAO;

abstract class AbstractDAO {

	abstract protected function getTable(): string;

	public function updateRaw(int $key, array $updates) {
		global $wpdb;

		$wpdb->update($this->getTable(), $updates, array('id' => $key), '%s', '%d');
	}

	/**
	 * @param string $fieldName
	 * @param string $fieldValue
	 *
	 * @return object|null
	 */
	protected function getByField(string $fieldName, string $fieldValue): ?object {
		global $wpdb;

		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE %i = %s;", $this->getTable(), $fieldName, $fieldValue));
		if (is_array($results) && count($results) > 0) {
			return $results[0];
		}

		return null;
	}

	/**
	 * @param array $fields
	 * @return object[]
	 */
	protected function getByFields(array $fields): array {
		global $wpdb;

		$conditions = [];
		foreach ($fields as $fieldName => $fieldValue) {
			$conditions[] = $wpdb->prepare("%i = %s", $fieldName, $fieldValue);
		}

		$imploded = implode(' AND ', $conditions);
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE $imploded", $this->getTable()));
		if (is_array($results)) {
			return $results;
		}

		return [];
	}

	/**
	 * @param array $conditions Percentage notation @see wpdb::prepare()
	 * @param array $arguments
	 */
	protected function deleteByConditions(array $conditions, array $arguments) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare("DELETE FROM %i WHERE ".implode(' AND ', $conditions), array_merge([$this->getTable()], $arguments));
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->get_results($sql);
	}

}