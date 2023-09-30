<?php

namespace Kainex\WiseAnalytics\Services\Users;

use Kainex\WiseAnalytics\DAO\UsersDAO;
use Kainex\WiseAnalytics\Model\User;
use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Utils\StringUtils;

class UsersService {

	const UUID_COOKIE = 'wa';
	const UUID_COOKIE_TIME = 15552000;  // 60 seconds * 60 minutes * 24 hours * 180 days = 6 months(ish)

	/** @var Options */
	private $options;

	/** @var UsersDAO */
	private $usersDAO;

	/**
	 * UsersService constructor.
	 * @param Options $options
	 * @param UsersDAO $usersDAO
	 */
	public function __construct(Options $options, UsersDAO $usersDAO)
	{
		$this->options = $options;
		$this->usersDAO = $usersDAO;
	}

	/**
	 * @return User
	 * @throws \Exception
	 */
	public function getOrCreateUser(): User {
		if (!isset($_COOKIE[UsersService::UUID_COOKIE])) {
			return $this->createUser();
		} else {
			$user = $this->usersDAO->getByUuid($_COOKIE[UsersService::UUID_COOKIE]);
			if (!$user) {
				return $this->createUser();
			}

			return $user;
		}
	}

	/**
	 * @return User
	 * @throws \Exception
	 */
	private function createUser(): User {
		$user = new User();
		$user->setUuid(StringUtils::createUuid());
		$user->setCreated(new \DateTime());
		$user = $this->usersDAO->save($user);

		setcookie(self::UUID_COOKIE, $user->getUuid(), time() + self::UUID_COOKIE_TIME, '/');
		$_COOKIE[self::UUID_COOKIE] = $user->getUuid();

		return $user;
	}


}