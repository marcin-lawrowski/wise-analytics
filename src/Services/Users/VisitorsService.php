<?php

namespace Kainex\WiseAnalytics\Services\Users;

use Kainex\WiseAnalytics\DAO\UsersDAO;
use Kainex\WiseAnalytics\Model\User;
use Kainex\WiseAnalytics\Options;
use Kainex\WiseAnalytics\Utils\StringUtils;

class VisitorsService {

	const UUID_COOKIE = 'wa';
	const UUID_COOKIE_TIME = 15552000;  // 60 seconds * 60 minutes * 24 hours * 180 days = 6 months(ish)

	private const FIELDS = [
		['id' => 'email', 'name' => 'E-mail'],
		['id' => 'firstName', 'name' => 'First Name'],
		['id' => 'lastName', 'name' => 'Last Name'],
	];

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
	public function getOrCreate(): User {
		if (!isset($_COOKIE[VisitorsService::UUID_COOKIE])) {
			return $this->create();
		} else {
			$visitor = $this->usersDAO->getByUuid($_COOKIE[VisitorsService::UUID_COOKIE]);
			if (!$visitor) {
				return $this->create();
			}

			return $visitor;
		}
	}

	public function save(User $user): User {
		return $this->usersDAO->save($user);
	}

	/**
	 * @return User
	 * @throws \Exception
	 */
	private function create(): User {
		$visitor = new User();
		$visitor->setUuid(StringUtils::createUuid());
		$visitor->setCreated(new \DateTime());
		$visitor = $this->usersDAO->save($visitor);

		setcookie(self::UUID_COOKIE, $visitor->getUuid(), time() + self::UUID_COOKIE_TIME, '/');
		$_COOKIE[self::UUID_COOKIE] = $visitor->getUuid();

		return $visitor;
	}

	/**
	 * @return string[][]
	 */
	public function getVisitorFields(): array {
		return self::FIELDS;
	}

	/**
	 * TODO: allow to set the strategy
	 *
	 * @param User $visitor
	 * @param array $update Plain update array
	 */
	public function saveWithPlainArray(User $visitor, array $update) {
		$updated = false;

		foreach ($update as $fieldId => $value) {
			if (!$value) {
				continue;
			}

			switch ($fieldId) {
				case 'email':
					$visitor->setEmail($value);
					// TODO: validation
					break;
				case 'firstName':
					$visitor->setFirstName($value);
					break;
				case 'lastName':
					$visitor->setLastName($value);
					break;
			}

			$updated = true;
		}

		if ($updated) {
			$this->save($visitor);
		}
	}

}