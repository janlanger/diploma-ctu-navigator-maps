<?php
namespace Maps\Model\Metadata\Queries;


use Maps\Model\Floor\Floor;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

/**
 * Active revision of floor
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class ActiveRevision extends QueryObjectBase {
    /** @var Floor[] */
    private $floor;

    /**
     * @param Floor|Floor[] $floor
     */
    function __construct($floor)
    {
        if(is_scalar($floor)) {
            $floor = [$floor];
        }
        $this->floor = $floor;
    }


    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository)
    {
        return $repository->createQueryBuilder("r")->select("r")
            ->where("r.floor IN (:floor)")
            ->andWhere("r.published = true")
            ->setParameter("floor", $this->floor);
    }
}