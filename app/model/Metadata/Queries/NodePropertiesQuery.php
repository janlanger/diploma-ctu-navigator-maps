<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.3.13
 * Time: 22:05
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class NodePropertiesQuery extends QueryObjectBase {

    private $in = [];

    function __construct($in)
    {
        $this->in = $in;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("p")->select("p")
                ->where("p.id IN (:in)")
                ->setParameter("in", $this->in);
    }
}