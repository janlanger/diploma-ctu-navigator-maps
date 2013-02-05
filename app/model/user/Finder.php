<?php
namespace Maps\Model\User;
use Maps\Model\BaseFinder;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Finder
 *
 * @author Honza
 */
class Finder extends BaseFinder {
    protected $alias = "u";


    public function where($what, $value) {
        $this->qb->where($this->alias.'.'.$what.'= :param')
                ->setParameter('param', $value);

        return $this;
    }

    public function getSingleResultWithRefresh() {
        try {
            return $this->qb->getQuery()->setHint(\Doctrine\ORM\Query::HINT_REFRESH, TRUE)->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }
    
}
