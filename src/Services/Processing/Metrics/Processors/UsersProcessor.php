<?php

namespace Kainex\WiseAnalytics\Services\Processing\Metrics\Processors;

use Kainex\WiseAnalytics\Model\Stats\Metric;
use Kainex\WiseAnalytics\Model\Stats\Statistic;

class UsersProcessor extends AbstractProcessor {

	protected function processDaily(Metric $metric) {
		$fromDate = date('Y-m-d 00:00:00');
		$toDate = date('Y-m-d 23:59:59');
		$events = $this->queryEvents(['COUNT(DISTINCT user_id) AS users'], ["created >= '$fromDate'", "created <= '$toDate'"]);

		$marker = new \DateTime();
		$marker->setTime(0, 0 ,0);
		$statistic = $this->statisticsDAO->getByTimeDimension('daily', $marker);
		if (!$statistic) {
			$statistic = new Statistic();
			$statistic->setInterval('daily');
			$statistic->setDimensions([]);
			$statistic->setMetricId($metric->getId());
			$statistic->setTimeDimension($marker);
		}

		$statistic->setMetricIntValue((int) $events[0]->users);
		$this->statisticsDAO->save($statistic);
	}

	protected function processWeekly(Metric $metric) {
		$nbDay = date('N');
		$monday = new \DateTime();
		$sunday = new \DateTime();
		$monday->modify('-'.($nbDay - 1).' days');
		$sunday->modify('+'.(7 - $nbDay).' days');

		$fromDate = $monday->format('Y-m-d 00:00:00');
		$toDate = $sunday->format('Y-m-d 23:59:59');
		$events = $this->queryEvents(['COUNT(DISTINCT user_id) AS users'], ["created >= '$fromDate'", "created <= '$toDate'"]);

		$monday->setTime(0, 0 ,0);
		$statistic = $this->statisticsDAO->getByTimeDimension('weekly', $monday);
		if (!$statistic) {
			$statistic = new Statistic();
			$statistic->setInterval('weekly');
			$statistic->setDimensions([]);
			$statistic->setMetricId($metric->getId());
			$statistic->setTimeDimension($monday);
		}

		$statistic->setMetricIntValue((int) $events[0]->users);
		$this->statisticsDAO->save($statistic);
	}

	protected function processMonthly(Metric $metric) {
		$firstDay = new \DateTime();
		$firstDay->modify('first day of this month');
		$lastDay = new \DateTime();
		$lastDay->modify('last day of this month');

		$fromDate = $firstDay->format('Y-m-d 00:00:00');
		$toDate = $lastDay->format('Y-m-d 23:59:59');
		$events = $this->queryEvents(['COUNT(DISTINCT user_id) AS users'], ["created >= '$fromDate'", "created <= '$toDate'"]);

		$firstDay->setTime(0, 0 ,0);
		$statistic = $this->statisticsDAO->getByTimeDimension('monthly', $firstDay);
		if (!$statistic) {
			$statistic = new Statistic();
			$statistic->setInterval('monthly');
			$statistic->setDimensions([]);
			$statistic->setMetricId($metric->getId());
			$statistic->setTimeDimension($firstDay);
		}

		$statistic->setMetricIntValue((int) $events[0]->users);
		$this->statisticsDAO->save($statistic);
	}
}