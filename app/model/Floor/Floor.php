<?php
namespace Maps\Model\Floor;
use Maps\Model\BaseEntity;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @Entity
 * @Table(name="floors")
 */
class Floor extends BaseEntity{
    /** @Column(type="string", length=50) */
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
