<?php

namespace Kainex\WiseAnalytics\Services\Processing;

use Kainex\WiseAnalytics\Options;

class ProcessingService {

	/** @var Options */
	private $options;

	/** @var MetricsService */
	private $metricsService;

	/** @var StatisticsService */
	private $statisticsService;

	/** @var SessionsService */
	private $sessionsService;

	/**
	 * ProcessingService constructor.
	 * @param Options $options
	 * @param MetricsService $metricsService
	 * @param StatisticsService $statisticsService
	 * @param SessionsService $sessionsService
	 */
	public function __construct(Options $options, MetricsService $metricsService, StatisticsService $statisticsService, SessionsService $sessionsService)
	{
		$this->options = $options;
		$this->metricsService = $metricsService;
		$this->statisticsService = $statisticsService;
		$this->sessionsService = $sessionsService;
	}

	public function process() {
		$this->sessionsService->refresh(new \DateTime());
	}

	private function processUsersMetric() {
		$this->statisticsService->process($this->metricsService->getOrCreate('users'));
	}

}