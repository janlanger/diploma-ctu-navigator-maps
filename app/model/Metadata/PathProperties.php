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
 * Class PathProperties
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_path_properties")
 */
class PathProperties extends BaseEntity {

    /**
     * @var NodeProperties
     * @ManyToOne(targetEntity="NodeProperties")
     * @JoinColumn(name="startNode", referencedColumnName="id", onDelete="CASCADE")
     */
    private $startNode;
    /**
     * @var NodeProperties
     * @ManyToOne(targetEntity="NodeProperties")
     * @JoinColumn(name="endNode", referencedColumnName="id", onDelete="CASCADE")
     */
    private $endNode;


    /**
     * @var bool
     * @column(type="boolean")
     */
    private $isFloorExchange = FALSE;

    /**
     * @var Floor
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", fetch="EAGER" )
     * @JoinColumn(name="destinationFloor", referencedColumnName="id", onDelete="SET NULL")
     */
    private $destinationFloor = NULL;

    /**
     * @param \Maps\Model\Metadata\Floor $destinationFloor
     */
    public function setDestinationFloor($destinationFloor) {
        $this->destinationFloor = $destinationFloor;
    }

    /**
     * @return \Maps\Model\Metadata\Floor
     */
    public function getDestinationFloor() {
        return $this->destinationFloor;
    }



    /**
     * @param \Maps\Model\Metadata\NodeProperties $endNode
     */
    public function setEndNode($endNode)
    {
        $this->endNode = $endNode;
    }

    /**
     * @return \Maps\Model\Metadata\NodeProperties
     */
    public function getEndNode()
    {
        return $this->endNode;
    }

    /**
     * @param \Maps\Model\Metadata\NodeProperties $startNode
     */
    public function setStartNode($startNode)
    {
        $this->startNode = $startNode;
    }

    /**
     * @return \Maps\Model\Metadata\NodeProperties
     */
    public function getStartNode()
    {
        return $this->startNode;
    }


    /**
     * @param boolean $isFloorExchange
     */
    public function setIsFloorExchange($isFloorExchange) {
        $this->isFloorExchange = $isFloorExchange;
    }

    /**
     * @return boolean
     */
    public function isFloorExchange() {
        return $this->isFloorExchange;
    }

    public function toArray() {
        return [
            "startNode" => $this->startNode->id,
            "endNode" => $this->endNode->id,
            "id" => $this->id,
        ];
    }
}