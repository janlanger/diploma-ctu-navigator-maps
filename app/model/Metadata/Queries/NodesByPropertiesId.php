<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Revision;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Nodes by properties ids
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class NodesByPropertiesId extends QueryObjectBase {
    /** @var int[] */
    private $ids;
    /** @var Revision */
    private $revision;

    /**
     * @param int[] $ids
     * @param Revision $revision
     */
    function __construct($ids, $revision)
    {
        $this->ids = $ids;
        $this->revision = $revision;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("n")->select("n")
                ->where("n.revision = :revision")
                ->andWhere("n.properties IN (:in)")
                ->setParameters(["revision" => $this->revision, 'in' => $this->ids]);
    }
}