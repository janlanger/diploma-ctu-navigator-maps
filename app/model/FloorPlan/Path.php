<?php
namespace Maps\Model\FloorPlan;
use Maps\Model\BaseEntity;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floorplan_paths", 
 *  uniqueConstraints={
 *      @UniqueConstraint(columns={"startNode", "endNode"})
 *  })
 */
class Path extends BaseEntity {
    /**
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="startNode", referencedColumnName="id")
     */
    private $startNode;
    /**
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="endNode", referencedColumnName="id")
     */
    private $endNode;
    /**
     * @ManyToOne(targetEntity="FloorPlan", inversedBy="paths")
     */
    private $floor;
}

?>
