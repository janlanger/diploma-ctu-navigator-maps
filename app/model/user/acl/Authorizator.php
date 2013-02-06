<?php
namespace Maps\Model\ACL;
use Nette\Security\Permission;
use Nette\Caching\Cache;

/**
 * Description of Authorizator
 *
 * @author Jan -Quinix- Langer
 */
class Authorizator extends Permission {
    //put your code here

    private function __construct(Service $service) {

        $roles=$service->getRoles();

        $resources=$service->getResources();
        $rules=$service->getRules();
        //dump($roles, $resources, $rules);
        foreach($roles as $role) {
            $this->addRole($role['name'], $role['parent_name']);
        }

        foreach ($resources as $resource) {
            $this->addResource($resource['name']);
        }

        foreach ($rules as $rule) {
            if($rule['allowed']) {
                $this->allow($rule['role'], $rule['resource'], $rule['privilege']);
            }
            else {
                $this->deny($rule['role'], $rule['resource'], $rule['privilege']);
            }
        }
    }

    /**
     *
     * @param Service $service
     * @param Cache $cache
     * @return Authorizator
     */
    public static function getInstance(Service $service, \Nette\Caching\IStorage $storage) {
        $cache = new Cache($storage, 'Acl');
        if(!$cache['acl']) {
            $instance=new self($service);
            $cache->save('acl', $instance,
                array(
                    Cache::TAGS=>array(
                        'Maps\\Model\\Acl\\Acl',
                        'Maps\\Model\\Acl\\Privilege',
                        'Maps\\Model\\Acl\\Resource',
                        'Maps\\Model\\Acl\\Roles',
                    )
                ));
            return $instance;
        }
        return $cache['acl'];

    }
}
