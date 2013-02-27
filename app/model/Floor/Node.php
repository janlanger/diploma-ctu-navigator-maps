<?php
namespace Maps\Model\Floor;
use Maps\Model\BaseEntity;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floorplan_nodes")
 */
class Node extends BaseEntity implements \JsonSerializable {
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", inversedBy="nodes")
     * @JoinColumn(name="floorplan_id", referencedColumnName="id")
     */
    private $floor_plan;
    /** @Column(type="string", length=100, nullable=false) */
    private $gps_coordinates;
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="string", length=32, nullable=true) */
    private $room;
    /** @Column(type="string", length=32,  nullable=false) */
    private $type;
    /** @Column(type="integer", nullable=true) */
    private $from_floor;
    /** @Column(type="integer", nullable=true) */
    private $to_floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\Building\Building")
     * @JoinColumn(name="to_building", referencedColumnName="id")
     */
    private $to_building;
    
    public function getFloorPlan() {
        return $this->floor_plan;
    }

    public function setFloorPlan($floor_plan) {
        $this->floor_plan = $floor_plan;
        return $this;
    }

    public function getGpsCoordinates() {
        return $this->gps_coordinates;
    }

    public function setGpsCoordinates($gps_coordinates) {
        $this->gps_coordinates = $gps_coordinates;
        return $this;
    }

    public function getPosition() {
        return $this->gps_coordinates;
    }

    public function setPosition($gps_coordinates) {
        $this->gps_coordinates = $gps_coordinates;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getFromFloor() {
        return $this->from_floor;
    }

    public function setFromFloor($from_floor) {
        if($from_floor == "") {
            $from_floor = null;
        }
        $this->from_floor = $from_floor;
        return $this;
    }

    public function getToFloor() {
        return $this->to_floor;
    }

    public function setToFloor($to_floor) {
        if($to_floor == "") {
            $to_floor = null;
        }
        $this->to_floor = $to_floor;
        return $this;
    }

    public function getToBuilding() {
        return $this->to_building;
    }

    public function setToBuilding($to_building) {
        $this->to_building = $to_building;
        return $this;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'position' => $this->gps_coordinates,
            'name' => $this->name,
            'type' => $this->type,
            'room' => $this->room,
            'fromFloor' => $this->from_floor,
            'toFloor' => $this->to_floor,
            'toBuilding' => ($this->to_building == null?null:$this->to_building->id),
        ];
    }


    
}

?>
