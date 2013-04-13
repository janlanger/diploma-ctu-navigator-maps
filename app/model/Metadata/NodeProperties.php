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
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="to_floor", referencedColumnName="id")
     */
    private $to_floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\Building\Building")
     * @JoinColumn(name="to_building", referencedColumnName="id")
     */
    private $to_building;


    /**
     * @deprecated
     * @param $from_floor
     */
    public function setFromFloor($from_floor) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->from_floor = $from_floor;
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getFromFloor() {
        return $this->from_floor;
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

    public function setRoom($room) {
        $this->room = $room;
    }

    public function getRoom() {
        return $this->room;
    }

    /**
     * @deprecated
     * @param $to_building
     */
    public function setToBuilding($to_building) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->to_building = $to_building;
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getToBuilding() {
        return $this->to_building;
    }

    /**
     * @deprecated
     * @param $to_floor
     */
    public function setToFloor($to_floor) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->to_floor = $to_floor;
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getToFloor() {
        return $this->to_floor;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function getPosition() {
        return $this->gps_coordinates;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'type' => $this->type,
            "toBuilding" => ($this->to_building != NULL ? $this->to_building->id : NULL),
            "gpsCoordinates" => $this->gps_coordinates,
            "name" => $this->name,
            "room" => $this->room,
            "fromFloor" => $this->from_floor,
            "toFloor" => $this->to_floor,
        ];
    }

    public function getReadableTitle() {
        $types = [
            'entrance' => 'Vchod',
            'stairs' => 'Schodiště',
            'elevator' => 'Výtah',
            'passage' => 'Průchod',
            'lecture' => 'Učebna',
            'office' => 'Kancelář',
            'study' => 'Studovna',
            'auditorium' => 'Posluchárna',
            'cafeteria' => 'Kantýna',
            'restroom-men' => 'WC muži',
            'restroom-women' => 'WC ženy',
            'cloakroom' => 'Šatna',
            'other' => '',
            'default' => '',
            'restriction' => 'Zákaz vstupu',
        ];
        if (!array_key_exists($this->type, $types)) {
            return NULL;
        }
        $title = $types[$this->type];
        if (in_array($this->type, ['lecture', 'auditorium', 'cafeteria', 'office']) && $this->room != "") {
            $title .= " - " . $this->room;
        }
        if ($this->name != "") {
            if ($title != "") {
                $title .= ": ";
            }
            $title .= $this->name;
        }
        if ($this->type == 'stairs' && $this->to_floor != NULL) {
            $title .= " do " . $this->to_floor->readableName;
        }
        if ($this->type == 'elevator' && $this->to_floor != NULL) {
            $title .= " " . $this->to_floor->readableName;
        }
        if ($this->type == "passage" && $this->to_building != NULL) {
            $title .= " do " . $this->to_building->name;
        }
        return $title;
    }

}