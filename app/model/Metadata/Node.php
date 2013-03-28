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
 * Class Node
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_nodes")
 */
class Node extends BaseEntity implements \JsonSerializable {

    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision", inversedBy="nodes")
     * @JoinColumn(name="revision_id", referencedColumnName="id", nullable=false)
     */
    private $revision;

    /**
     * @var NodeProperties
     * @ManyToOne(targetEntity="NodeProperties", fetch="EAGER", cascade={"persist"})
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

    /**
     * @param \Maps\Model\Metadata\NodeProperties $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return \Maps\Model\Metadata\NodeProperties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \Maps\Model\Metadata\Revision $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * @return \Maps\Model\Metadata\Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }


    /**
     * (PHP 5 >= 5.4.0)
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed Returns data which can be serialized by json_encode(), which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'propertyId' => $this->properties->id,
            'position' => $this->properties->getGpsCoordinates(),
            'name' => $this->properties->getName(),
            'type' => $this->properties->getType(),
            'room' => $this->properties->getRoom(),
            'fromFloor' => $this->properties->getFromFloor(),
            'toFloor' => $this->properties->getToFloor(),
            'toBuilding' => ($this->properties->getToBuilding() == null ? null : $this->properties->getToBuilding()->id),
        ];
    }
}