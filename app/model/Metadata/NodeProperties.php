<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:25
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;

use Maps\Model\BaseEntity;

/**
 * Class NodeProperties
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_node_properties")
 */
class NodeProperties extends BaseEntity {
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

    public function setFromFloor($from_floor)
    {
        $this->from_floor = $from_floor;
    }

    public function getFromFloor()
    {
        return $this->from_floor;
    }

    public function setGpsCoordinates($gps_coordinates)
    {
        $this->gps_coordinates = $gps_coordinates;
    }

    public function getGpsCoordinates()
    {
        return $this->gps_coordinates;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRoom($room)
    {
        $this->room = $room;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setToBuilding($to_building)
    {
        $this->to_building = $to_building;
    }

    public function getToBuilding()
    {
        return $this->to_building;
    }

    public function setToFloor($to_floor)
    {
        $this->to_floor = $to_floor;
    }

    public function getToFloor()
    {
        return $this->to_floor;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPosition() {
        return $this->gps_coordinates;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'type' => $this->type,
            "toBuilding" => ($this->to_building != null?$this->to_building->id: null),
            "gpsCoordinates" => $this->gps_coordinates,
            "name" => $this->name,
            "room" => $this->room,
            "fromFloor" => $this->from_floor,
            "toFloor" => $this->to_floor,
        ];
    }

}