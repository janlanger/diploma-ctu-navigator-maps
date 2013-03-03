<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 28.2.13
 * Time: 18:59
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\BaseEntity;

/**
 * Class Plan
 * @package Maps\Model\Floor
 * @Entity
 * @Table(name="floor_plans")
 */

class Plan extends BaseEntity{
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", inversedBy="nodes")
     * @JoinColumn(name="floor_id", referencedColumnName="id")
     */
    private $floor;
    /** @Column(type="integer") */
    private $revision = 1;
    /** @Column(type="boolean") */
    private $published = false;

    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     */
    private $user;

    /** @Column(type="string",length=200) */
    private $plan;

    /** @Column(type="datetime") */
    private $added_date;
    /** @Column(type="datetime", nullable=true) */
    private $published_date;

    public function __construct() {
        $this->added_date = new \DateTime();
    }

    public function setFloor($floor) {
        $this->floor = $floor;
    }

    public function getFloor() {
        return $this->floor;
    }

    public function setPlan($plan) {
        $this->plan = $plan;
    }

    public function getPlan() {
        return $this->plan;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }




}