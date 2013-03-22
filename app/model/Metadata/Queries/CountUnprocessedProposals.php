<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 22.3.13
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class CountUnprocessedProposals extends QueryObjectBase {

    private $metadata;



    public function __construct($metadata) {
        $this->metadata = $metadata;
    }



    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("c")->select("count(c.id)")
            ->where("c.against_revision = :revision")
            ->setParameter("revision", $this->metadata);
    }
}