<?php

namespace Kainex\WiseAnalytics\DAO\Stats;

use Kainex\WiseAnalytics\DAO\AbstractDAO;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Model\Stats\Metric;
use Kainex\WiseAnalytics\Options;

/**
 * MetricsDAO.
 *
 * @author Kainex <contact@kaine.pl>
 */
class MetricsDAO extends AbstractDAO {
	
	/** @var Options */
	private $options;

	protected function getTable(): string {
		return Installer::getMetricsTable();
	}

	public function __construct(Options $options) {
		$this->options = $options;
	}

	/**
	 * @param integer $id
	 *
	 * @return Metric|null
	 */
	public function get(int $id): ?Metric {
		return $this->populateData($this->getByField('id', (string) $id));
	}

	/**
	 * @param string $slug
	 *
	 * @return Metric|null
	 */
	public function getBySlug(string $slug): ?Metric {
		return $this->populateData($this->getByField('slug', (string) $slug));
	}

	/**
	 * @param Metric $metric
	 *
	 * @return Metric
	 * @throws \Exception On validation error
	 */
	public function save(Metric $metric): Metric {
		global $wpdb;

		if ($metric->getSlug() === null) {
			throw new \Exception('Empty slug');
		}
		if ($metric->getName() === null) {
			throw new \Exception('Empty name');
		}

		$columns = array(
			'slug' => $metric->getSlug(),
			'name' => $metric->getName()
		);

		if ($metric->getId() !== null) {
			$wpdb->update($this->getTable(), $columns, array('id' => $metric->getId()), '%s', '%d');
		} else {
			$wpdb->insert($this->getTable(), $columns);
			$metric->setId($wpdb->insert_id);
		}

		return $metric;
	}

	/**
	 * @param object $rawData
	 *
	 * @return Metric
	 */
	private function populateData(?object $rawData): ?Metric {
		if (!$rawData) {
			return null;
		}
		$metric = new Metric();
		$metric->setId(intval($rawData->id));
		$metric->setSlug($rawData->slug);
		$metric->setName($rawData->name);

		return $metric;
	}

}