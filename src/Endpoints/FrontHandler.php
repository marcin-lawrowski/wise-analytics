<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Services\Events\EventsService;
use Kainex\WiseAnalytics\Services\Processing\ProcessingService;
use Kainex\WiseAnalytics\Services\Users\UsersService;

/**
 * FrontHandler
 *
 * @author Kainex <contact@kainex.pl>
 */
class FrontHandler {
	
	/**  @var Options */
	private $options;
	
	/** @var UsersService */
	private $usersService;

	/** @var EventsService */
	private $eventsService;

	/**
	 * FrontHandler constructor.
	 * @param Options $options
	 * @param UsersService $usersService
	 * @param EventsService $eventsService
	 */
	public function __construct(Options $options, UsersService $usersService, EventsService $eventsService)
	{
		$this->options = $options;
		$this->usersService = $usersService;
		$this->eventsService = $eventsService;
	}

	public function registerEndpoints() {
		$this->registerEventsEndpoint();
	}
	
	public function registerHandlers($query) {
		if (isset($query->query_vars['_wa_api_action'])) {
			$this->handleRequest($query->query_vars['_wa_api_action']);
			die();
		}
	}
	
	/**
	 * Registers API endpoints using rewrite rules.
     */
	private function registerEventsEndpoint() {
		add_rewrite_tag('%_wa_api_action%', '([a-zA-Z\d\-_+]+)');
		add_rewrite_rule('wa-api/([a-zA-Z\d\-_+]+)/?', 'index.php?_wa_api_action=$matches[1]', 'top');
	}
	
	private function handleRequest($command) {
		switch ($command) {
			case 'event';
				$this->handleEvent();
				break;
		}
	}
	
	private function handleEvent() {
		try {
			$user = $this->usersService->getOrCreateUser();
			$event = $this->eventsService->createEvent(
				$user,
				$this->getRequestParam('ty'),
				$this->getRequestParam('cs'), [
					'uri' => $this->getRequestParam('ur'),
					'title' => $this->getRequestParam('ti'),
					'referer' => $this->getRequestParam('re'),
				]
			);

			$this->endResponse("event ok: " . $event->getId());
		} catch (\Exception $e) {
			$this->endResponse("event error: " . $e->getMessage());
		}
	}

	/**
	 * Returns GET parameter value.
	 *
	 * @param string $name
	 * @param string $default
	 *
	 * @return mixed
	 */
	private function getRequestParam($name, $default = null) {
		return array_key_exists($name, $_GET) ? stripslashes_deep($_GET[$name]) : $default;
	}

	/**
	 * Outputs 1x1 transparent GIF image together with "X-WA-Api-Response" header
	 *
	 * @param string $headerMessage
	 */
	private function endResponse($headerMessage = null) {
		$imageSource = 'R0lGODlhAQABAID/AP///wAAACwAAAAAAQABAAACAkQBADs=';
		$image = base64_decode($imageSource);
		nocache_headers();
		header("Content-type: image/gif");
		header("Content-Length: ".strlen($image));
		if ($headerMessage) {
			header("X-NeptuneWeb-Api-Response: {$headerMessage}");
		}
		echo $image;
		die();
	}
	
}