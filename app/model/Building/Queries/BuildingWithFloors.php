<?php

namespace Maps\Model\Building\Queries;


use Doctrine\ORM\Query\Expr\Join;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Loads buildings with all their floors
 *
 * @package Maps\Model\Building\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class BuildingWithFloors extends QueryObjectBase {

    private $id;

    /**
     * @param int $id requested building ID; if its not provided, all buildings will be loaded
     */
    function __construct($id=NULL) {
        $this->id = $id;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("b")
                ->select("b")
                ->innerJoin("Maps\\Model\\Floor\\Floor", "f", Join::WITH, "f.building = b");
        if($this->id != NULL) {
            $q->where("b.id = ?1")
                ->setParameter(1, $this->id);
        }
        return $q;
    }
}