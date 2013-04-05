<?php
namespace Maps\Model\Floor;
use Doctrine\ORM\Query\AST\Join;
use Maps\Model\Persistence\QueryObjectBase;
use Doctrine\ORM\Query\Expr;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlanDatagridQuery
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class FloorsDatagridQuery extends QueryObjectBase {
    
    private $building_id;
    
    function __construct($building_id) {
        $this->building_id = $building_id;
    }

    
    protected function doCreateQuery(\Maps\Model\Persistence\IQueryable $repository) {
        return $repository->createQueryBuilder("f")
                ->select("f,p.revision AS plan, m.revision AS metadata")
                ->leftJoin("Maps\\Model\\Floor\\Plan","p", Expr\Join::WITH, 'p.floor = f AND p.published = TRUE' )
                ->leftJoin("Maps\\Model\\Metadata\\Revision", "m", Expr\Join::WITH, 'm.floor = f AND m.published = TRUE')
                ->where("f.building = ?1")
                ->setParameter(1, $this->building_id);

    }    
    
}

?>
