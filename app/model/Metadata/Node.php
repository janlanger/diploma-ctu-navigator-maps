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
class Node extends BaseEntity {

    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision")
     * @JoinColumn(name="against_revision", referencedColumnName="id", nullable=false)
     */
    private $revision;

    /**
     * @var NodeProperties
     * @OneToOne(targetEntity="NodeProperties")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

}