<?php
namespace Maps\Model\FloorPlan;
use Maps\Model\BaseEntity;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floorplan_nodes")
 */
class Node extends BaseEntity {
    /**
     * @ManyToOne(targetEntity="Maps\Model\FloorPlan\FloorPlan")
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
    
}

?>
