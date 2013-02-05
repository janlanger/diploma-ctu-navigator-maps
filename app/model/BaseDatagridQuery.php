<?php
namespace Maps\Model;
use Maps\Model\Persistence\QueryObjectBase;
use Maps\Model\Persistence\IQueryable;
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.2.13
 * Time: 20:01
 * To change this template use File | Settings | File Templates.
 */
class BaseDatagridQuery extends QueryObjectBase {

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $qb = $repository->createQueryBuilder("b")->select();
        return $qb;
    }
}
