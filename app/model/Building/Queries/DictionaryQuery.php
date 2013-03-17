<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 17.3.13
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Building;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class DictionaryQuery extends QueryObjectBase {

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("b")->select("b.id, b.name");
    }
}