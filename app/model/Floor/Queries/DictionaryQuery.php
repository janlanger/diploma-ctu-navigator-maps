<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 17.3.13
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Doctrine\ORM\Query\Expr;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class DictionaryQuery extends QueryObjectBase {

    private $building;

    function __construct($building = NULL) {
        $this->building = $building;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
       /* $q = $repository->createQuery("SELECT f.id, COALESCE(f.name, CONCAT(f.floor_number, ' NP')) as name FROM ".__NAMESPACE__.'\\Floor f WHERE f.building = ?1')
        ->setParameter(1, $this->building);
        return $q;*/
        $q = $repository->createQueryBuilder("f")->select("f.id");
        $q->addSelect(new Expr\Func('COALESCE', ['f.name','f.floor_number'])." as name");
        if($this->building) {
            $q->where("f.building = ?1")
                ->setParameter(1, $this->building);
        }
        return $q;
    }
}