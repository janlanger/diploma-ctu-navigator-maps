<?php
namespace Maps\Model\Floor;
use DateTime;
use Maps\Model\BaseEntity;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floors")
 * @HasLifecycleCallbacks
 */
class Floor extends BaseEntity {
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="integer") */
    private $floor_number;
    /** 
     * @ManyToOne(targetEntity="Maps\Model\Building\Building", inversedBy="floors", cascade={"persist"})
     * @JoinColumn(name="building_id", referencedColumnName="id")
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

    function __construct() {
        $this->lastUpdate = new DateTime();
    }


    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getFloorNumber() {
        return $this->floor_number;
    }

    public function setFloorNumber($floor_number) {
        $this->floor_number = $floor_number;
    }

    public function getBuilding() {
        return $this->building;
    }

    public function setBuilding($building) {
        $this->building = $building;
    }

    public function getFloorPlan() {
        return $this->floor_plan;
    }

    public function setFloorPlan($floor_plan) {
        $this->floor_plan = $floor_plan;
    }

    public function getTiles() {
        return $this->tiles;
    }

    public function setTiles($tiles) {
        $this->tiles = $tiles;
    }
    
    public function getNodes() {
        return $this->nodes;
    }

    public function getPaths() {
        return $this->paths;
    }

    public function getReadableName() {
        if($this->name != "") {
            return $this->name;
        }
        else {
            return "č. ".$this->floor_number;
        }
    }

    public function setFloorHeight($floorHeight) {
        $this->floorHeight = $floorHeight;
    }

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
