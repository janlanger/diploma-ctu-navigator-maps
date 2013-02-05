<?php
namespace Maps\Model\ACL;
use Maps\Model\Persistence\IQueryable;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.2.13
 * Time: 23:37
 * To change this template use File | Settings | File Templates.
 */
class DatagridQuery extends \Maps\Model\Persistence\QueryObjectBase {

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $qb = $repository->createQueryBuilder("r")->select("r, r1.name")
            ->leftJoin("r.parent","r1");
        return $qb;
    }
}
