<?php
namespace Maps\Model\Building;
/**
 * @Entity
 * @table(name="building")
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
}
