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
class Path extends BaseEntity implements \JsonSerializable {
    /**
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="startNode", referencedColumnName="id", onDelete="CASCADE")
     */
    private $startNode;
    /**
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="endNode", referencedColumnName="id", onDelete="CASCADE")
     */
    private $endNode;
    /**
     * @ManyToOne(targetEntity="FloorPlan", inversedBy="paths")
     */
    private $floor;
    
    public function getStartNode() {
        return $this->startNode;
    }

    public function setStartNode($startNode) {
        $this->startNode = $startNode;
        return $this;
    }

    public function getEndNode() {
        return $this->endNode;
    }

    public function setEndNode($endNode) {
        $this->endNode = $endNode;
        return $this;
    }

    public function getFloor() {
        return $this->floor;
    }

    public function setFloor($floor) {
        $this->floor = $floor;
        return $this;
    }

    public function jsonSerialize() {
        return [
            'startNode' => $this->startNode,
            'endNode' => $this->endNode,
        ];
    }


}

?>