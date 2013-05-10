<?php

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;

/**
 * Class Path
 * @package Maps\Model\Metadata
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @Entity
 * @table(name="metadata_paths")
 */
class Path extends BaseEntity {
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision", inversedBy="paths")
     * @JoinColumn(name="revision_id",referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $revision;
    /**
     * @var PathProperties
     * @ManyToOne(targetEntity="PathProperties", fetch="EAGER", cascade={"persist"})
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

    /**
     * @var float
     * @Column(type="float", nullable=true)
     */
    private $length;

    /**
     * @param \Maps\Model\Metadata\PathProperties $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return \Maps\Model\Metadata\PathProperties
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
     * @param float $length
     */
    public function setLength($length) {
        $this->length = $length;
    }

    /**
     * @return float
     */
    public function getLength() {
        return $this->length;
    }




}