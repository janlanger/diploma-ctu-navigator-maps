<?php
namespace Maps\Model\Floor;
use DateTime;
use Maps\Model\BaseEntity;
use Maps\Model\Building\Building;

/**
 * Floor representation
 *
 * @Entity
 * @Table(name="floors")
 * @HasLifecycleCallbacks
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Model\Floor
 */
class Floor extends BaseEntity {
    /**
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $floor_number;
    /** 
     * @ManyToOne(targetEntity="Maps\Model\Building\Building", inversedBy="floors", cascade={"persist"})
     * @JoinColumn(name="building_id", referencedColumnName="id")
     * @var Building
     */
    private $building;

    /**
     * @var float
     * @Column(type="float",nullable=false)
     */
    private $floorHeight = 0;

    /**
     * @var DateTime
     * @Column(type="datetime", nullable=false)
     */
    private $lastUpdate;

    /**
     */
    function __construct() {
        $this->lastUpdate = new DateTime();
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
     * @return int
     */
    public function getFloorNumber() {
        return $this->floor_number;
    }

    /**
     * @param int $floor_number
     */
    public function setFloorNumber($floor_number) {
        $this->floor_number = $floor_number;
    }

    /**
     * @return Building
     */
    public function getBuilding() {
        return $this->building;
    }

    /**
     * @param Building $building
     */
    public function setBuilding($building) {
        $this->building = $building;
    }

    /**
     * readable name of floor
     * @return string
     */
    public function getReadableName() {
        if($this->name != "") {
            return $this->name;
        }
        else {
            return "č. ".$this->floor_number;
        }
    }

    /**
     * @param float $floorHeight
     */
    public function setFloorHeight($floorHeight) {
        $this->floorHeight = $floorHeight;
    }

    /**
     * @return float|int
     */
    public function getFloorHeight() {
        return $this->floorHeight;
    }

    /**
     * @PreUpdate
     */
    public function preUpdate(){
        $this->lastUpdate = new DateTime();
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

}

?>
