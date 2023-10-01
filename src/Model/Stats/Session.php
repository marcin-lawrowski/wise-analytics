<?php

namespace Kainex\WiseAnalytics\Model\Stats;

class Session {

	 /** @var integer|null */
    private $id;

    /** @var integer */
    private $userId;

    /** @var \DateTime */
    private $start;

    /** @var \DateTime */
    private $end;

	/** @var array */
    private $events;

    /** @var integer */
    private $duration;

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @param int|null $id
	 */
	public function setId(?int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}

	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	/**
	 * @return \DateTime
	 */
	public function getStart(): \DateTime
	{
		return $this->start;
	}

	/**
	 * @param \DateTime $start
	 */
	public function setStart(\DateTime $start): void
	{
		$this->start = $start;
	}

	/**
	 * @return \DateTime
	 */
	public function getEnd(): \DateTime
	{
		return $this->end;
	}

	/**
	 * @param \DateTime $end
	 */
	public function setEnd(\DateTime $end): void
	{
		$this->end = $end;
	}

	/**
	 * @return array
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * @param array $events
	 */
	public function setEvents(array $events): void
	{
		$this->events = $events;
	}

	/**
	 * @return int
	 */
	public function getDuration(): int
	{
		return $this->duration;
	}

	/**
	 * @param int $duration
	 */
	public function setDuration(int $duration): void
	{
		$this->duration = $duration;
	}

}