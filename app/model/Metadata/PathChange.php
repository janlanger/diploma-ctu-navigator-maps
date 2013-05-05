<?php

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;

/**
 * Class PathChange
 * @package Maps\Model\Metadata
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @Entity
 * @Table(name="metadata_path_change")
 */
class PathChange extends BaseEntity {

    /**
     * @var Changeset
     * @ManyToOne(targetEntity="Changeset", cascade={"persist"})
     * @JoinColumn(name="changeset_id", referencedColumnName="id", nullable=false,onDelete="CASCADE")
     */
    private $changeset;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $was_deleted = FALSE;

    /**
     * @var PathProperties
     * @OneToOne(targetEntity="PathProperties", cascade={"persist"}, fetch="EAGER")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=true)
     */
    private $properties;

    /**
     * @var PathProperties
     * @ManyToOne(targetEntity="PathProperties", fetch="EAGER")
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
     * @param \Maps\Model\Metadata\Path $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return \Maps\Model\Metadata\Path
     */
    public function getOriginal()
    {
        return $this->original;
    }

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