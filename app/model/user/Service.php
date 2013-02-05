<?php

namespace Maps\Model\User;

use Maps\Model\BaseService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Service
 *
 * @author Honza
 */
class Service extends BaseService {

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        parent::__construct($em, __NAMESPACE__ . '\\User');
    }

    public function getFinder() {
        return new Finder($this);
    }

    public function getDictionary() {
        return $this->getFinder()->fetchPairs('id', 'nick');
    }

    public function getUserByLogin($login) {
        return $this->getFinder()->where('username', $login)->getSingleResultWithRefresh();
    }

}
