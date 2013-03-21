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

    /** @Column(type="float") */
    private $length;

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

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function getLength()
    {
        return $this->length;
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



}