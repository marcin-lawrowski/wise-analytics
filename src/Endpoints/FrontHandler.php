<?php

namespace Kainex\WiseAnalytics\Endpoints;

use Kainex\WiseAnalytics\DAO\EventTypesDAO;
use Kainex\WiseAnalytics\DAO\UsersDAO;
use Kainex\WiseAnalytics\Model\User;
use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Services\Events\EventsService;
use Kainex\WiseAnalytics\Services\Stats\ProcessingService;
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

	/** @var ProcessingService */
	private $processingService;

	/**
	 * FrontHandler constructor.
	 * @param Options $options
	 * @param UsersService $usersService
	 * @param EventsService $eventsService
	 * @param ProcessingService $processingService
	 */
	public function __construct(Options $options, UsersService $usersService, EventsService $eventsService, ProcessingService $processingService)
	{
		$this->options = $options;
		$this->usersService = $usersService;
		$this->eventsService = $eventsService;
		$this->processingService = $processingService;
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
		$user = $this->usersService->getOrCreateUser();

		/*
		$event = $this->eventsService->createEvent(
			$user,
			$this->getRequestParam('ty'),
			$this->getRequestParam('cs'), [
				'uri' => $this->getRequestParam('ur'),
				'title' => $this->getRequestParam('ti'),
				'referer' => $this->getRequestParam('re'),
			]
		);
		*/

		$this->processingService->process();


		//echo $event->getId();
		//$this->usersDao->save($user);
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
	
}