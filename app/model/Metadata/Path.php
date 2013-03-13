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
     * @ManyToOne(targetEntity="Revision")
     * @JoinColumn(name="revision_id",referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $revision;
    /**
     * @var PathProperties
     * @OneToOne(targetEntity="PathProperties")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

}