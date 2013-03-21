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
 * Class NodeChange
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_node_change")
 */
class NodeChange extends BaseEntity {

    /**
     * @var Changeset
     * @ManyToOne(targetEntity="Changeset", cascade={"persist"})
     * @JoinColumn(name="changeset_id", referencedColumnName="id", nullable=false)
     */
    private $changeset;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $was_deleted = false;

    /**
     * @var NodeProperties
     * @OneToOne(targetEntity="NodeProperties", cascade={"persist"})
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=true)
     */
    private $properties;

    /**
     * @var Node
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     */
    private $original;

    /**
     * @param \Maps\Model\Metadata\Changeset $changeset
     */
    public function setChangeset($changeset)
    {
        $this->changeset = $changeset;
    }

    /**
     * @return \Maps\Model\Metadata\Changeset
     */
    public function getChangeset()
    {
        return $this->changeset;
    }

    /**
     * @param \Maps\Model\Metadata\Node $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return \Maps\Model\Metadata\Node
     */
    public function getOriginal()
    {
        return $this->original;
    }

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
     * @param boolean $was_deleted
     */
    public function setWasDeleted($was_deleted)
    {
        $this->was_deleted = $was_deleted;
    }

    /**
     * @return boolean
     */
    public function getWasDeleted()
    {
        return $this->was_deleted;
    }





}