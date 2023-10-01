<?php

namespace Kainex\WiseAnalytics\Services\Processing;

use Kainex\WiseAnalytics\Container;
use Kainex\WiseAnalytics\Model\Stats\Metric;
use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Services\Processing\Metrics\Processors\UsersProcessor;

class StatisticsService {

	/** @var Options */
	private $options;

	/** @var Container */
	private $container;

	/**
	 * StatisticsService constructor.
	 * @param Options $options
	 * @param Container $container
	 */
	public function __construct(Options $options)
	{
		$this->options = $options;
	}

	public function process(Metric $metric) {
		switch ($metric->getSlug()) {
			case 'users':
				/** @var UsersProcessor $processor */
				//$processor = $this->container->get('services.stats.statistics.metrics.users');
				//$processor->process($metric);
				break;
		}
	}

}