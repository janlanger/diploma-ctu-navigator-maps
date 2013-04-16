<?php
namespace Maps\Model\Building;
use DateTime;
use Maps\Model\Floor\Floor;

/**
 * @Entity
 * @table(name="building")
 * @HasLifecycleCallbacks
 */
class Building extends \Maps\Model\BaseEntity{
    /**  @Column(type="string", length=50) */
    private $name;
    /** @Column(type="string", length=200) */
    private $address;
    /** @Column(type="integer") */
    private $floor_count;
    /**  @Column(type="string", length=10, nullable=true) */
    private $room_prefix;
    /** @Column(type="string", length=200, nullable=true) */
    private $gps_coordinates;

    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=false)
     */
    private $lastUpdate;

    /**
     * @var Floor[]
     * @OneToMany(targetEntity="Maps\Model\Floor\Floor", mappedBy="building")
     */
    private $floors;

    function __construct() {
        $this->lastUpdate = new DateTime();
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setFloorCount($floor_count) {
        $this->floor_count = $floor_count;
    }

    public function getFloorCount() {
        return $this->floor_count;
    }

    public function setGpsCoordinates($gps_coordinates) {
        $this->gps_coordinates = $gps_coordinates;
    }

    public function getGpsCoordinates() {
        return $this->gps_coordinates;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setRoomPrefix($room_prefix) {
        $this->room_prefix = $room_prefix;
    }

    public function getRoomPrefix() {
        return $this->room_prefix;
    }

    public function getFloors() {
        return $this->floors;
    }

    public function setFloors($floors) {
        $this->floors = $floors;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate($lastUpdate) {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate() {
        return $this->lastUpdate;
    }

    /**
     * @PreUpdate
     */
    public function preUpdate() {
        $this->lastUpdate = new DateTime();
    }

}
