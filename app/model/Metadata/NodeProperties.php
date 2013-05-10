<?php

namespace Maps\Model\Metadata;

use Maps\Model\BaseEntity;
use Maps\Model\Building\Building;
use Maps\Model\Floor\Floor;

/**
 * Class NodeProperties
 * @package Maps\Model\Metadata
 * @author Jan Langer <langeja1@fit.cvut.cz>
 *
 * @Entity
 * @Table(name="metadata_node_properties")
 */
class NodeProperties extends BaseEntity {
    /**
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    private $gps_coordinates;
    /**
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    private $name;
    /**
     * @var string
     * @Column(type="string", length=32, nullable=true)
     */
    private $room;
    /**
     * @var string
     * @Column(type="string", length=32,  nullable=false)
     */
    private $type;
    /**
     * @deprecated
     * @var int
     * @Column(type="integer", nullable=true)
     */
    private $from_floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="to_floor", referencedColumnName="id")
     * @deprecated
     * @var Floor
     */
    private $to_floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\Building\Building")
     * @JoinColumn(name="to_building", referencedColumnName="id")
     * @deprecated
     * @var Building
     */
    private $to_building;


    /**
     * @deprecated
     * @param int $from_floor
     */
    public function setFromFloor($from_floor) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->from_floor = $from_floor;
    }

    /**
     * @deprecated
     * @return int
     */
    public function getFromFloor() {
        return $this->from_floor;
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
    public function getGpsCoordinates() {
        return $this->gps_coordinates;
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $room
     */
    public function setRoom($room) {
        $this->room = $room;
    }

    /**
     * @return string
     */
    public function getRoom() {
        return $this->room;
    }

    /**
     * @deprecated
     * @param Building $to_building
     */
    public function setToBuilding($to_building) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->to_building = $to_building;
    }

    /**
     * @deprecated
     * @return Building
     */
    public function getToBuilding() {
        return $this->to_building;
    }

    /**
     * @deprecated
     * @param Floor $to_floor
     */
    public function setToFloor($to_floor) {
        trigger_error('Node properties fromFloor, toFloor and toBuilding was deprecated. Use search using paths definition.', E_USER_DEPRECATED);
        //BC using prePersist event
        $this->to_floor = $to_floor;
    }

    /**
     * @deprecated
     * @return Floor
     */
    public function getToFloor() {
        return $this->to_floor;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPosition() {
        return $this->gps_coordinates;
    }

    /**
     * Returns instance variables as array
     * @return array
     */
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

    /**
     * @return string
     */
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