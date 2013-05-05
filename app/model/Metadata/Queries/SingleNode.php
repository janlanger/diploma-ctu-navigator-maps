<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Fetch single node with its revision and properties
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class SingleNode extends QueryObjectBase {
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    function __construct($id) {
        $this->id = $id;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        return $repository->createQueryBuilder("n")
                ->innerJoin("n.properties", "p")
                ->innerJoin("n.revision", "r")
                ->where("r.published = true")
                ->andWhere("p.id = :id")
                ->setParameter("id", $this->id);
    }
}