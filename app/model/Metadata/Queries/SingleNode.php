<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 16.4.13
 * Time: 0:19
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class SingleNode extends QueryObjectBase {

    private $id;

    function __construct($id) {
        $this->id = $id;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("n")
                ->innerJoin("n.properties", "p")
                ->innerJoin("n.revision", "r")
                ->where("r.published = true")
                ->andWhere("p.id = :id")
                ->setParameter("id", $this->id);
    }
}