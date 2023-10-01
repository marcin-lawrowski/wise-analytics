<?php

namespace Kainex\WiseAnalytics\DAO\Stats;

use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\Stats\Statistic;
use Kainex\WiseAnalytics\Options;

/**
 * StatisticsDAO.
 *
 * @author Kainex <contact@kaine.pl>
 */
class StatisticsDAO {
	
	/** @var Options */
	private $options;

	public function __construct(Options $options) {
		$this->options = $options;
	}

	/**
	 * @param string $interval
	 * @param integer $id
	 *
	 * @return Statistic|null
	 * @throws \Exception
	 */
	public function get(string $interval, int $id): ?Statistic {
		global $wpdb;

		$sql = sprintf("SELECT * FROM `%s` WHERE `id` = '%s';", $this->getTable($interval), $id);
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateData($interval, $results[0]);
		}

		return null;
	}

	/**
	 * @param string $interval
	 * @param \DateTime $time
	 * @return Statistic|null
	 * @throws \Exception
	 */
	public function getByTimeDimension(string $interval, \DateTime $time): ?Statistic {
		global $wpdb;

		$sql = sprintf("SELECT * FROM `%s` WHERE `time_dimension` = '%s';", $this->getTable($interval), $time->format('Y-m-d H:i:s'));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateData($interval, $results[0]);
		}

		return null;
	}

	/**
	 * @param Statistic $statistic
	 *
	 * @return Statistic
	 * @throws \Exception On validation error
	 */
	public function save(Statistic $statistic): Statistic {
		global $wpdb;

		if ($statistic->getInterval() === null) {
			throw new \Exception('Empty interval');
		}
		if ($statistic->getMetricId() === null) {
			throw new \Exception('Empty metric ID');
		}
		if ($statistic->getTimeDimension() === null) {
			throw new \Exception('Empty time dimension');
		}

		$columns = [
			'time_dimension' => $statistic->getTimeDimension()->format('Y-m-d H:i:s'),
			'metric_id' => $statistic->getMetricId(),
			'metric_int_value' => $statistic->getMetricIntValue(),
			'dimensions' => json_encode($statistic->getDimensions()),
		];

		if ($statistic->getId() !== null) {
			$wpdb->update($this->getTable($statistic->getInterval()), $columns, array('id' => $statistic->getId()), '%s', '%d');
		} else {
			$wpdb->insert($this->getTable($statistic->getInterval()), $columns);
			$statistic->setId($wpdb->insert_id);
		}

		return $statistic;
	}

	/**
	 * @param string $interval
	 * @param object $rawData
	 *
	 * @return Statistic|null
	 */
	private function populateData(string $interval, ?object $rawData): ?Statistic {
		if (!$rawData) {
			return null;
		}
		$statistic = new Statistic();
		$statistic->setInterval($interval);
		$statistic->setId(intval($rawData->id));
		$statistic->setTimeDimension(\DateTime::createFromFormat('Y-m-d H:i:s', $rawData->time_dimension));
		$statistic->setMetricId($rawData->metric_id);
		$statistic->setMetricIntValue($rawData->metric_int_value);
		$statistic->setDimensions(json_decode($rawData->dimensions, true));

		return $statistic;
	}

	private function getTable(string $interval): string {
		switch ($interval) {
			case 'daily':
				return Installer::getDailyStatsTable();
				break;
			case 'monthly':
				return Installer::getMonthlyStatsTable();
				break;
			case 'weekly':
				return Installer::getWeeklyStatsTable();
				break;
		}

		throw new \Exception('Unsupported interval');
	}

}