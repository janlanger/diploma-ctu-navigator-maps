<?php

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\Revision;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;
use Maps\Model\User\User;

/**
 * Non processed proposals for floor
 *
 * @package Maps\Model\Metadata\Queries
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class ActiveProposals extends QueryObjectBase {

    /** @var User */
    private $user;
    /** @var Revision */
    private $revision;

    /**
     * @param User $user
     * @param Revision $revision
     */
    function __construct($user=NULL, $revision = NULL) {
        $this->user = $user;
        $this->revision = $revision;
    }



    /** {@inheritdoc} */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("c")->select("c, u, n, p")
            ->innerJoin("c.submitted_by", 'u')
            ->leftJoin('c.nodes', 'n')
            ->leftJoin('c.paths', 'p')
            ->where("c.state = :state")
            ->setParameter("state", Changeset::STATE_NEW)
            ->orderBy("c.submitted_date", 'desc');
        if($this->user != NULL) {
            $q->andWhere("c.submitted_by = :user")
                ->setParameter('user',$this->user);
        } else {
            $q->innerJoin("c.against_revision", "r");
            $q->andWhere("r.floor = :floor")
                ->setParameter("floor", $this->revision->floor);
        }

        return $q;
    }
}