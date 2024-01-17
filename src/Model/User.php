<?php

namespace Kainex\WiseAnalytics\Model;

/**
 * Wise Analytics user model.
 */
class User {
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $uuid;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $company;

    /** @var array|null */
    private $data;

    /** @var string|null */
    private $language;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * WiseAnalyticsUser constructor.
     */
    public function __construct() {
        $this->data = array();
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }
	
    /**
     * @return array|null
     */
    public function getData(): ?array {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data) {
        $this->data = $data;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

	/**
	 * @return string|null
	 */
	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 */
	public function setFirstName(?string $firstName): void
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 */
	public function setLastName(?string $lastName): void
	{
		$this->lastName = $lastName;
	}

	/**
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}

	/**
	 * @param string|null $email
	 */
	public function setEmail(?string $email): void
	{
		$this->email = $email;
	}

	/**
	 * @return string|null
	 */
	public function getCompany(): ?string
	{
		return $this->company;
	}

	/**
	 * @param string|null $company
	 */
	public function setCompany(?string $company): void
	{
		$this->company = $company;
	}

	/**
	 * @return string|null
	 */
	public function getLanguage(): ?string
	{
		return $this->language;
	}

	/**
	 * @param string|null $language
	 */
	public function setLanguage(?string $language): void
	{
		$this->language = $language;
	}

}