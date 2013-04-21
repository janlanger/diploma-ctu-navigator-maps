<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 20.4.13
 * Time: 21:40
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;

use Maps\Model\BaseEntity;
use Maps\Tools\Mixed;

/**
 * Class FloorConnection
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_floor_connections")
 */
class FloorConnection extends BaseEntity {

    const STAIRS_SLOPE_ESTIMATE = 35;

    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision", cascade={"persist"})
     */
    private $revision_one;
    /**
     * @var NodeProperties
     * @ManyToOne(targetEntity="NodeProperties")
     */
    private $node_one;
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision", cascade={"persist"})
     */
    private $revision_two;
    /**
     * @var NodeProperties
     * @ManyToOne(targetEntity="NodeProperties")
     */
    private $node_two;
    /**
     * @var string
     * @Column(type="string", length=50)
     */
    private $type;
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $created;

    function __construct() {
        $this->created = new \DateTime();
    }

    /**
     * @param \Maps\Model\Metadata\NodeProperties $node_one
     */
    public function setNodeOne($node_one) {
        $this->node_one = $node_one;
    }

    /**
     * @return \Maps\Model\Metadata\NodeProperties
     */
    public function getNodeOne() {
        return $this->node_one;
    }

    /**
     * @param \Maps\Model\Metadata\NodeProperties $node_two
     */
    public function setNodeTwo($node_two) {
        $this->node_two = $node_two;
    }

    /**
     * @return \Maps\Model\Metadata\NodeProperties
     */
    public function getNodeTwo() {
        return $this->node_two;
    }

    /**
     * @param \Maps\Model\Metadata\Revision $revision_one
     */
    public function setRevisionOne($revision_one) {
        $this->revision_one = $revision_one;
    }

    /**
     * @return \Maps\Model\Metadata\Revision
     */
    public function getRevisionOne() {
        return $this->revision_one;
    }

    /**
     * @param \Maps\Model\Metadata\Revision $revision_two
     */
    public function setRevisionTwo($revision_two) {
        $this->revision_two = $revision_two;
    }

    /**
     * @return \Maps\Model\Metadata\Revision
     */
    public function getRevisionTwo() {
        return $this->revision_two;
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
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Return estimated connection length for stairs based on class constant. For passage returs
     * distance between point coordinates. Return 0 for elevator and when floor height is not set.
     * @return float
     */
    public function estimatedLength() {
        if($this->type == 'elevator') {
            return 0;
        }
        if($this->type == 'passage') {
            return Mixed::calculateDistanceBetweenGPS($this->node_one->getGpsCoordinates(), $this->node_two->getGpsCoordinates());
        }
        if($this->type == 'stairs') {
            $floorHeight = $this->revision_one->getFloor()->floorHeight;
            if($floorHeight < 0.5) {
                return 0;
            }
            return round($floorHeight / sin(deg2rad(self::STAIRS_SLOPE_ESTIMATE)),4);
        }
        return 0;
    }
}