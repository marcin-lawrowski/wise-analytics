<?php

namespace Kainex\WiseAnalytics\Integrations\Plugins;

use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Services\Events\EventsService;
use Kainex\WiseAnalytics\Services\Users\VisitorsService;
use Kainex\WiseAnalytics\Utils\IPUtils;
use Kainex\WiseAnalytics\Utils\URLUtils;

class ContactForm7 {

	/** @var Options */
	private $options;

	/** @var VisitorsService */
	private $visitorsService;

	/** @var EventsService */
	private $eventsService;

	/**
	 * ContactForm7 constructor.
	 * @param Options $options
	 * @param VisitorsService $visitorsService
	 * @param EventsService $eventsService
	 */
	public function __construct(Options $options, VisitorsService $visitorsService, EventsService $eventsService)
	{
		$this->options = $options;
		$this->visitorsService = $visitorsService;
		$this->eventsService = $eventsService;
	}

	public function install() {
		add_action('wpcf7_before_send_mail', [$this, 'beforeSendEmail']);
	}

	public function beforeSendEmail($WPCF7_ContactForm) {
		$submission = \WPCF7_Submission::get_instance();
		$postedData = $submission->get_posted_data();
		$visitor = $this->visitorsService->getOrCreate();
		$this->eventsService->createEvent(
			$visitor,
			'form-submission', [
				'uri' => URLUtils::getCurrentURL(),
				'ip' => IPUtils::getIpAddress(),
				'submission' => $postedData
			]
		);

		$mappingKey = 'plugins.cf7.form.'.$WPCF7_ContactForm->id;
		$visitorsConfiguration = (array) $this->options->getOption('visitors', []);
		$currentMappings = isset($visitorsConfiguration['mappings']) && isset($visitorsConfiguration['mappings'][$mappingKey]) ? $visitorsConfiguration['mappings'][$mappingKey] : null;

		if (!$currentMappings) {
			return;
		}

		$visitorUpdate = [];
		foreach ($currentMappings as $actionFieldId => $visitorFieldId) {
			if ($visitorFieldId && isset($postedData[$actionFieldId]) && $postedData[$actionFieldId]) {
				$visitorUpdate[$visitorFieldId] = $postedData[$actionFieldId];
			}
		}

		$this->visitorsService->saveWithPlainArray($visitor, $visitorUpdate);

		var_dump($visitorUpdate);die();
	}

}