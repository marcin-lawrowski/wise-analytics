<?php

namespace Kainex\WiseAnalytics\Services\Processing\Metrics\Processors;

use Kainex\WiseAnalytics\DAO\Stats\StatisticsDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\Stats\Metric;

abstract class AbstractProcessor {

	/** @var StatisticsDAO */
	protected $statisticsDAO;

	/**
	 * AbstractProcessor constructor.
	 * @param StatisticsDAO $statisticsDAO
	 */
	public function __construct(StatisticsDAO $statisticsDAO)
	{
		$this->statisticsDAO = $statisticsDAO;
	}

	abstract protected function processDaily(Metric $metric);
	abstract protected function processWeekly(Metric $metric);
	abstract protected function processMonthly(Metric $metric);

	public function process(Metric $metric) {
		$this->processDaily($metric);
		$this->processWeekly($metric);
		$this->processMonthly($metric);
	}

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