<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 15.4.13
 * Time: 15:53
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Building\Queries;


use Doctrine\ORM\Query\Expr\Join;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class BuildingWithFloors extends QueryObjectBase {

    private $id;

    function __construct($id=NULL) {
        $this->id = $id;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("b")
                ->select("b")
                ->innerJoin("Maps\\Model\\Floor\\Floor", "f", Join::WITH, "f.building = b");
        if($this->id != null) {
            $q->where("b.id = ?1")
                ->setParameter(1, $this->id);
        }
        return $q;
    }
}