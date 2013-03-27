<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.3.13
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class PathsByPropertiesId extends QueryObjectBase {

    private $ids;
    private $revision;

    function __construct($ids, $revision)
    {
        $this->ids = $ids;
        $this->revision = $revision;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("n")->select("n")
                ->where("n.revision = :revision")
                ->andWhere("n.properties IN (:in)")
                ->setParameters(["revision" => $this->revision, 'in' => $this->ids]);
    }
}