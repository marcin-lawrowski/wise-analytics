<?php


namespace Kainex\WiseAnalytics\Services\Stats;


use Kainex\WiseAnalytics\DAO\Stats\MetricsDAO;
use Kainex\WiseAnalytics\Model\Stats\Metric;
use Kainex\WiseAnalytics\Options;

class MetricsService {

	const STANDARD_TYPES = [
		'users' => 'Users'
	];

	/** @var Options */
	private $options;

	/** @var MetricsDAO */
	private $metricsDAO;

	/**
	 * MetricsService constructor.
	 * @param Options $options
	 * @param MetricsDAO $metricsDAO
	 */
	public function __construct(Options $options, MetricsDAO $metricsDAO)
	{
		$this->options = $options;
		$this->metricsDAO = $metricsDAO;
	}

	/**
	 * @param string $slug
	 * @return Metric
	 * @throws \Exception
	 */
	public function getOrCreate(string $slug): Metric {
		$metric = $this->metricsDAO->getBySlug($slug);
		if (!$metric) {
			if (isset(self::STANDARD_TYPES[$slug])) {
				$metric = new Metric();
				$metric->setName(self::STANDARD_TYPES[$slug]);
				$metric->setSlug($slug);
				$metric = $this->metricsDAO->save($metric);
			} else {
				throw new \Exception('Unsupported metric');
			}
		}

		return $metric;
	}

}