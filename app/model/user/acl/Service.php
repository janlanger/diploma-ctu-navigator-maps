<?php

namespace Maps\Model\Acl;

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

    public function __construct(\Doctrine\ORM\EntityManager $em, $entityName = NULL) {
        if ($entityName == NULL) {
            $entityName = __NAMESPACE__ . '\\Role';
        }
        parent::__construct($em, $entityName);
    }

    public function getFinder() {
        return new Finder($this);
    }

    public function getRules($id = NULL) {
        return $this->getFinder()->getAcl($id)->getArrayResult();
    }

    public function getRoles() {
        return $this->getFinder()->getRoles()->getArrayResult();
    }

    public function getResources() {
        return $this->getFinder()->getResources()->getArrayResult();
    }

    public function getDictionary() {
        return $this->getFinder()->fetchPairs('id', 'name');
    }

    protected function setData($entity, $values) {
        if (isset($values['parent'])) {
            $values['parent'] = $this->find($values['parent']);
        }
        parent::setData($entity, $values);
    }

    public function updateRolePrivilege($role, $values) {
        $em = $this->getEntityManager();

        $actual = $this->getFinder()->getRoleAcl($role)->getResult();
        $privileges = new CachedLoader($this, 'getPrivilege');

        $processed = array();
        $nullPrivilege = array();
        /* @var $data \Doctrine\ORM\PersistentCollection */
        
            $data = $actual[0]->getAcl();
            
            //update
            foreach ($data as $k => $acl) { //walk through Acl entity array
                $resource = $acl->getResource()->getName();
                foreach ($values as $key => $value) {    //walk through form values
                    if ($resource == $value['resource']) {   //if you match resource - update it
                        if ($value['privilege'] == NULL) {   //if privilege is NULL (all actions)
                            $acl->setPrivilege(NULL);
                            $nullPrivilege[$value['resource']] = $k;
                        } elseif ($acl->getPrivilege() == NULL || $acl->getPrivilege()->getName() != $value['privilege']) { //else-if action is diferent
                            //if(!isset($privileges[$value['privilege']])) {  //if privilege entity is not in cache - load it
                            //     $privileges[$value['privilege']] = $this->getFinder()->getPrivilege($value['privilege'], TRUE);
                            //}
                            $acl->setPrivilege($privileges->get($value['privilege'], TRUE)); //then update privilege definition
                        }
                        $acl->setAllowed($value['allowed']>0);    //update allowed filed - true/false
                        $processed[] = $k; //add it to DB queue
                        unset($values[$key]); //delete it from form queue

                        break;
                    }
                }
            }
        
        if (count($processed) < $data->count()) {
            //delete
            
            foreach ($data as $key => $value) {
                
                
                if (!in_array($key, $processed)) {
                
                    $em->remove($value);
                    $data->removeElement($value);
                }
            }
            
        }
        
        $resources = new CachedLoader($this, 'getResource');
        if (count($values) > 0) {
            //insert
            $roleEntity = $this->find($role);
            foreach ($values as $value) {
                $item = new Acl();
                $item->setAllowed($value['allowed']);
                $item->setPrivilege($value['privilege'] == NULL?NULL:$privileges->get($value['privilege'], TRUE));
                $item->setResource($resources->get($value['resource'], TRUE));
                $item->setRole($roleEntity);
                $data->add($item);
            }
        }
        //check for duplicate all privileges/specific data
        foreach ($data as $key => $acl) {
            if($acl->getPrivilege() != NULL && isset($nullPrivilege[$acl->getResource()->getName()])) {
                if($data->get($nullPrivilege[$acl->getResource()->getName()])->isAllowed() == $acl->isAllowed()) {
                    $em->remove($acl);
                    $data->removeElement($acl);
                }
            }
        }
        try{
            $em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
        
    }

}

class CachedLoader {

    private $service;
    private $cache = array();
    private $method;

    function __construct($service, $method) {
        $this->service = $service;
        $this->method = $method;
    }

    public function get($key, $createIfNotExists = FALSE) {
        if (!isset($this->cache[$key])) {
            $item = $this->service->getFinder()->{$this->method}($key, $createIfNotExists);
            $this->cache[$key] = $item;
        }
        return $this->cache[$key];
    }

}
