<?php

namespace Maps\Model\Acl;

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

    protected $alias = 'r';

    /** @var Service $secvice */
    private $service;

    public function __construct($service) {
        $this->service=$service;
        parent::__construct($service);
    }

    public function getAcl($id = NULL) {
        $rsm=new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('allowed', 'allowed');
        $rsm->addScalarResult('role', 'role');
        $rsm->addScalarResult('resource', 'resource');
        $rsm->addScalarResult('privilege', 'privilege');



        $q=$this->service->getEntityManager()->createNativeQuery(
                "SELECT a.allowed AS allowed, ro.name as role, re.name AS resource, p.name as privilege FROM acl a
                    JOIN acl_roles ro ON (a.role_id = ro.id)
                    LEFT JOIN acl_resources re ON (a.resource_id = re.id)
                    LEFT JOIN acl_privileges p ON (a.privilege_id = p.id)"
                    .($id != NULL?" WHERE ro.id = ".$id:"").
                    " ORDER BY a.id ASC",
                $rsm);

        return $q;
    }

    public function getRoles() {
        $rsm=new \Doctrine\ORM\Query\ResultSetMapping();

        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('parent_name', 'parent_name');


        $q=$this->service->getEntityManager()->createNativeQuery("SELECT r1.name AS name, r2.name as parent_name
            FROM acl_roles r1 LEFT JOIN acl_roles r2 ON (r1.parent_id=r2.id)
            ORDER by r1.parent_id ASC", $rsm);

        return $q;

    }

    public function getPrivileges() {
        $rsm=new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');


        $q=$this->service->getEntityManager()->createNativeQuery("SELECT id, name FROM acl_privileges", $rsm);

        return $q;

    }

    public function getResources() {
        $rsm=new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');


        $q=$this->service->getEntityManager()->createNativeQuery("SELECT id, name FROM acl_resources", $rsm);

        return $q;

    }
    
    public function getForDatagrid() {
        $this->qb->select("r, r1.name")->leftjoin('r.parent', 'r1');
                
        

        return $this->qb;
    }
    
    public function getRoleAcl($id) {
        
        $this->qb->select('acl, r, privilege, res')
                ->leftjoin("r.acl", "acl")
                //->join('acl.role', 'role')
                ->leftjoin('acl.privilege', 'privilege')
                ->leftjoin('acl.resource', 'res')
                ->where('r.id= :id')
                ->setParameter('id', $id);
        return $this;
    }
    
    public function getPrivilege($name, $createIfNotExists) {
        $qb = $this->service->getEntityManager()->getRepository('Maps\\Model\\Acl\\Privilege')->createQueryBuilder('p');
        $qb->select('p')->where('p.name = :name')->setParameter('name', $name);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch(\Doctrine\ORM\NoResultException $e) {
            if($createIfNotExists) {
                $privilege = new Privilege();
                $privilege->setName($name);
                return $privilege;
            }
        }
        return null;
    }
    
    public function getResource($name, $createIfNotExists) {
        $qb = $this->service->getEntityManager()->getRepository('Maps\\Model\\Acl\\Resource')->createQueryBuilder('r');
        $qb->select('r')->where('r.name = :name')->setParameter('name', $name);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch(\Doctrine\ORM\NoResultException $e) {
            if($createIfNotExists) {
                $privilege = new Resource();
                $privilege->setName($name);
                return $privilege;
            }
        }
        return null;
    }


}
