<?php


namespace Kainex\WiseAnalytics\Model\Stats;

class Statistic {

	 /** @var integer|null */
    private $id;

    /** @var string */
    private $interval;

    /** @var \DateTime */
    private $timeDimension;

    /** @var int */
    private $metricId;

    /** @var int */
    private $metricIntValue;

    /** @var array */
    private $dimensions;

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
	 * @return string|null
	 */
	public function getInterval(): ?string
	{
		return $this->interval;
	}

	/**
	 * @param string $interval
	 */
	public function setInterval(string $interval): void
	{
		$this->interval = $interval;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getTimeDimension(): ?\DateTime
	{
		return $this->timeDimension;
	}

	/**
	 * @param \DateTime $timeDimension
	 */
	public function setTimeDimension(\DateTime $timeDimension): void
	{
		$this->timeDimension = $timeDimension;
	}

	/**
	 * @return int|null
	 */
	public function getMetricId(): ?int
	{
		return $this->metricId;
	}

	/**
	 * @param int $metricId
	 */
	public function setMetricId(int $metricId): void
	{
		$this->metricId = $metricId;
	}

	/**
	 * @return int
	 */
	public function getMetricIntValue(): int
	{
		return $this->metricIntValue;
	}

	/**
	 * @param int $metricIntValue
	 */
	public function setMetricIntValue(int $metricIntValue): void
	{
		$this->metricIntValue = $metricIntValue;
	}

	/**
	 * @return array
	 */
	public function getDimensions(): array
	{
		return $this->dimensions;
	}

	/**
	 * @param array $dimensions
	 */
	public function setDimensions(array $dimensions): void
	{
		$this->dimensions = $dimensions;
	}

}