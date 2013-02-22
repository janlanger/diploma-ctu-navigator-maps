<?php
namespace Maps\Model\FloorPlan;
use Maps\Model\BaseEntity;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floor_plans")
 */
class FloorPlan extends BaseEntity {
    /**
     * @Column(type="integer", nullable=false)
     */
    private $version;    
    /** @Column(type="string", length=50, nullable=true) */
    private $name;
    /** @Column(type="integer") */
    private $floor_number;
    /** 
     * @ManyToOne(targetEntity="Maps\Model\Building\Building", cascade={"all"})
     * @JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;
    /** @Column(type="string", length=200, nullable=true) */
    private $tiles;
}

?>
