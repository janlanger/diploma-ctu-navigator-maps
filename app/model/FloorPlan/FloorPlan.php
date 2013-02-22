<?php
namespace Maps\Model\FloorPlan;
use Maps\Model\BaseEntity;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floor_plans")
 */
class FloorPlan extends BaseEntity {
    /**
     * @Column(type="integer", nullable=false)
     */
    private $version = 1;    
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="integer") */
    private $floor_number;
    /** 
     * @ManyToOne(targetEntity="Maps\Model\Building\Building", cascade={"all"})
     * @JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;
    
    /** @Column(type="string", length=200, nullable=true) */
    private $floor_plan;
    /** @Column(type="string", length=200, nullable=true) */
    private $tiles;
    
    /**
     * @OneToMany(targetEntity="Node", mappedBy="floor_plan", cascade={"persist"}, orphanRemoval=true)
     */
    private $nodes = [];
    
    /**
     * @OneToMany(targetEntity="Path", mappedBy="floor", cascade={"persist"}, orphanRemoval=true)
     */
    private $paths = [];
    
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
    
}

?>
