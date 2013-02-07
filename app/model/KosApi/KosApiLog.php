<?php
namespace Maps\Model\KosApi;
use Maps\Model\BaseEntity;
/**
 * @Entity
 * @Table(name="kosapi_log")
 */
class KosApiLog extends BaseEntity{
    /**
     * @manyToOne(targetEntity="\Maps\Model\Building\Building")
     * @JoinColumn(name="building_id", referencedColumnName="id", onDelete="RESTRICT")
     **/
    private $building;
    /** @Column(type="datetime")  */
    private $timestamp;
    /** @Column(type="boolean")  */
    private $state;
    /** @Column(type="string", length=200, nullable=true)  */
    private $msg;
}
