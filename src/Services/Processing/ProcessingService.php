<?php

namespace Kainex\WiseAnalytics\Services\Processing;

use Kainex\WiseAnalytics\Options;

class ProcessingService {

	/** @var Options */
	private $options;

	/** @var SessionsService */
	private $sessionsService;

	/**
	 * ProcessingService constructor.
	 * @param Options $options
	 * @param SessionsService $sessionsService
	 */
	public function __construct(Options $options, SessionsService $sessionsService)
	{
		$this->options = $options;
		$this->sessionsService = $sessionsService;
	}

	public function process() {
		$this->sessionsService->refresh(new \DateTime());
	}

}