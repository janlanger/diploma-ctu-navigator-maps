<?php
namespace Maps\Model\KosApi;
use Maps\Model\BaseEntity;
/**
 * @entity
 * @table("room_list")
 */
class Room extends BaseEntity{

    const TYPE_LECTURE = 'lecture';
    const TYPE_AUDITORIUM = 'auditorium';
    const TYPE_CAFETERIA = 'cafeteria';
    const TYPE_RESTROOM = 'restroom';
    const TYPE_OFFICE = 'office';

    /**
     * @manyToOne(targetEntity="\Maps\Model\Building\Building")
     * @JoinColumn(name="building_id", referencedColumnName="id", onDelete="RESTRICT")
     **/
    private $building;
    /**
     * @Column(type="string",length=20)
     */
    private $code;
    /**
     * @Column(type="string", length=30)
     */
    private $type;

    public function setBuilding($building) {
        $this->building = $building;
    }

    public function getBuilding() {
        return $this->building;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function getCode() {
        return $this->code;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }


}
