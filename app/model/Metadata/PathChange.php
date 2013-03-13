<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:26
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;

/**
 * Class PathChange
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_path_change")
 */
class PathChange extends BaseEntity {

    /**
     * @var Changeset
     * @ManyToOne(targetEntity="Changeset")
     * @JoinColumn(name="changeset_id", referencedColumnName="id", nullable=false)
     */
    private $changeset;
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    private $was_deleted = false;

    /**
     * @var PathProperties
     * @OneToOne(targetEntity="PathProperties")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

    /**
     * @var Path
     * @ManyToOne(targetEntity="Path")
     * @JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     */
    private $original;
}