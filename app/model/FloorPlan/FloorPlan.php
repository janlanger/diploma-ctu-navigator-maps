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
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="floor_id", referencedColumnName="id")
     */
    private $floor;
    /**
     * @Column(type="integer", nullable=false)
     */
    private $version;
    /** @Column(type="string", length=200, nullable=true) */
    private $plan_source;
}

?>
