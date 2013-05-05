<?php
namespace Maps\Model\Building;
use DateTime;
use Maps\Model\Floor\Floor;

/**
 * Building representation
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Building
 *
 * @Entity
 * @table(name="building")
 * @HasLifecycleCallbacks
 */
class Building extends \Maps\Model\BaseEntity {
    /**
     * @var string
     * @Column(type="string", length=50)
     */
    private $name;
    /**
     * @var string
     * @Column(type="string", length=200)
     */
    private $address;
    /**
     * @var int
     * @Column(type="integer")
     */
    private $floor_count;
    /**
     * @var string
     * @Column(type="string", length=10, nullable=true)
     */
    private $room_prefix;
    /**
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
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

    /**
     */
    function __construct() {
        $this->lastUpdate = new DateTime();
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * @return int
     */
    public function getFloorCount() {
        return $this->floor_count;
    }

    /**
     * @param int $floor_count
     */
    public function setFloorCount($floor_count) {
        $this->floor_count = $floor_count;
    }

    /**
     * @return string
     */
    public function getGpsCoordinates() {
        return $this->gps_coordinates;
    }

    /**
     * @param string $gps_coordinates
     */
    public function setGpsCoordinates($gps_coordinates) {
        $this->gps_coordinates = $gps_coordinates;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRoomPrefix() {
        return $this->room_prefix;
    }

    /**
     * @param string $room_prefix
     */
    public function setRoomPrefix($room_prefix) {
        $this->room_prefix = $room_prefix;
    }

    /**
     * @return Floor[]
     */
    public function getFloors() {
        return $this->floors;
    }

    /**
     * @param Floor[] $floors
     */
    public function setFloors($floors) {
        $this->floors = $floors;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate() {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate($lastUpdate) {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @PreUpdate
     */
    public function preUpdate() {
        $this->lastUpdate = new DateTime();
    }

}
