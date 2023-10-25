<?php

namespace Kainex\WiseAnalytics\Integrations;

use Kainex\WiseAnalytics\Services\Events\EventsService;
use Kainex\WiseAnalytics\Services\Users\UsersService;
use Kainex\WiseAnalytics\Utils\IPUtils;
use Kainex\WiseAnalytics\Utils\URLUtils;

class WordPressIntegrations {

	/** @var UsersService */
	private $usersService;

	/** @var EventsService */
	private $eventsService;

	/**
	 * WordPressIntegrations constructor.
	 * @param UsersService $usersService
	 * @param EventsService $eventsService
	 */
	public function __construct(UsersService $usersService, EventsService $eventsService)
	{
		$this->usersService = $usersService;
		$this->eventsService = $eventsService;
	}

	public function install() {
		add_action('wp_login', [$this, 'onWpLogin'], 10, 2);
	}

	/**
	 * Maps WordPress user details to visitor record. Registers "log in" event with details.
	 *
	 * @param string $userLogin
	 * @param \WP_User $wpUser
	 * @throws \Exception
	 */
	public function onWpLogin(string $userLogin, \WP_User $wpUser) {
		$user = $this->usersService->getOrCreateUser();
		if ($wpUser->first_name) {
			$user->setFirstName($wpUser->first_name);
		}
		if ($wpUser->last_name) {
			$user->setLastName($wpUser->last_name);
		}
		$user->setEmail($wpUser->user_email);
		$this->usersService->saveUser($user);

		$this->eventsService->createEvent(
			$user,
			'wp-user-log-in', [
				'uri' => URLUtils::getCurrentURL(),
				'ip' => IPUtils::getIpAddress(),
				'id' => $wpUser->ID,
				'login' => $userLogin,
			]
		);
	}

}