<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:19
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;

/**
 * Class Path
 * @package Maps\Model\Metadata
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
     * @OneToOne(targetEntity="PathProperties", fetch="EAGER")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

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



}