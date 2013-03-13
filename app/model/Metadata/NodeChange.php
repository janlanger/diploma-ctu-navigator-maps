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
     * @var NodeProperties
     * @OneToOne(targetEntity="NodeProperties")
     * @JoinColumn(name="properties_id", referencedColumnName="id", nullable=false)
     */
    private $properties;

    /**
     * @var Node
     * @ManyToOne(targetEntity="Node")
     * @JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     */
    private $original;



}