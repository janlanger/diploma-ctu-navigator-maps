<?php
namespace Maps\Model\KosApi;
use Maps\Model\Persistence\QueryObjectBase;
use Maps\Model\Persistence\IQueryable;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 7.2.13
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */
class LastCommunicationQuery extends QueryObjectBase{

    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $qb = $repository->createQueryBuilder("l")->orderBy("l.timestamp","desc")->setMaxResults(1);
        return $qb;
    }
}
