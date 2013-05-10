<?php
namespace Maps\Model\Floor\Queries;
use Doctrine\ORM\Query\AST\Join;
use Maps\Model\Persistence\QueryObjectBase;
use Doctrine\ORM\Query\Expr;


/**
 * Fetch all floors by building id
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class FloorsDatagridQuery extends QueryObjectBase {
    /** @var int  */
    private $building_id;

    /**
     * @param int $building_id
     */
    function __construct($building_id) {
        $this->building_id = $building_id;
    }

    /** {@inheritdoc} */
    protected function doCreateQuery(\Maps\Model\Persistence\IQueryable $repository) {
        return $repository->createQueryBuilder("f")
                ->select("f,p.revision AS plan, m.revision AS metadata, count(c.id) as proposals")
                ->leftJoin("Maps\\Model\\Floor\\Plan","p", Expr\Join::WITH, 'p.floor = f AND p.published = TRUE' )
                ->leftJoin("Maps\\Model\\Metadata\\Revision", "m", Expr\Join::WITH, 'm.floor = f AND m.published = TRUE')
                ->leftJoin("Maps\\Model\\Metadata\\Changeset", "c", Expr\Join::WITH, 'c.against_revision = m AND c.state = \'new\' ')
                ->where("f.building = ?1")
                ->groupBy("f.id")
                ->setParameter(1, $this->building_id);

    }    
    
}

?>
