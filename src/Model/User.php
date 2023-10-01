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

    /**
     * @var array
     */
    private $data;

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
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data) {
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
}