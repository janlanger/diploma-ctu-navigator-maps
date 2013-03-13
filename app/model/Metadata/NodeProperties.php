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
 * Class NodeProperties
 * @package Maps\Model\Metadata
 * @Entity
 * @Table(name="metadata_node_properties")
 */
class NodeProperties extends BaseEntity {
    /** @Column(type="string", length=100, nullable=false) */
    private $gps_coordinates;
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="string", length=32, nullable=true) */
    private $room;
    /** @Column(type="string", length=32,  nullable=false) */
    private $type;
    /** @Column(type="integer", nullable=true) */
    private $from_floor;
    /** @Column(type="integer", nullable=true) */
    private $to_floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\Building\Building")
     * @JoinColumn(name="to_building", referencedColumnName="id")
     */
    private $to_building;

}