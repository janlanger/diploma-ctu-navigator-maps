<?php
namespace Maps\Model\FloorPlan;
use Maps\Model\Persistence\QueryObjectBase;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlanDatagridQuery
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class PlanDatagridQuery extends QueryObjectBase {
    
    private $building_id;
    
    function __construct($building_id) {
        $this->building_id = $building_id;
    }

    
    protected function doCreateQuery(\Maps\Model\Persistence\IQueryable $repository) {
        return $repository->createQueryBuilder("b")
                ->select()
                ->where("b.building = :building")
                ->setParameter('building', $this->building_id);
        
        
    }    
    
}

?>
