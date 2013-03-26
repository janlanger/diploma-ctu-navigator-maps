<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 21.3.13
 * Time: 22:22
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata\Queries;


use Maps\Model\Metadata\Changeset;
use Maps\Model\Persistence\IQueryable;
use Maps\Model\Persistence\QueryObjectBase;

class ActiveProposals extends QueryObjectBase {

    private $user;



    function __construct($user=NULL) {
        $this->user = $user;
    }



    /**
     * @param IQueryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(IQueryable $repository) {
        $q = $repository->createQueryBuilder("c")->select("c, u, n, p")
            ->innerJoin("c.submitted_by", 'u')
            ->leftJoin('c.nodes', 'n')
            ->leftJoin('c.paths', 'p')
            ->where("c.state = :state")
            ->setParameter("state", Changeset::STATE_NEW)
        ->orderBy("c.submitted_date", 'desc');
        if($this->user != NULL) {
            $q->where("c.submitted_by = :user")
                ->setParameter('user',$this->user);
        }
        return $q;
    }
}