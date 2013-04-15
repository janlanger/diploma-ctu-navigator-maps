<?php
namespace Maps\Model\Floor;
use Maps\Model\BaseEntity;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floors")
 */
class Floor extends BaseEntity {
    /**
     * @Column(type="integer", nullable=false)
     */
    private $version = 1;    
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="integer") */
    private $floor_number;
    /** 
     * @ManyToOne(targetEntity="Maps\Model\Building\Building", inversedBy="floors", cascade={"persist"})
     * @JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;


    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
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
    
}

?>
