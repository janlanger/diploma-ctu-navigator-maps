<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 8.3.13
 * Time: 16:42
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class DeactivatePlansOfFloorQuery extends QueryObjectBase {
    /** @var Floor */
    private $floor;

    function __construct(Floor $floor) {
        $this->floor = $floor;
    }


    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("p");
        $q->update(__NAMESPACE__.'\\Plan','p')
            ->set("p.published", $q->expr()->literal(false))
            ->where("p.floor = :floor")
            ->andWhere("p.published = :published")
            ->setParameter('floor', $this->floor)
            ->setParameter("published", true);

        return $q;
    }
}