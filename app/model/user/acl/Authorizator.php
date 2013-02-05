<?php
namespace Maps\Model\ACL;
use Nette\Caching\Cache;

class Authorizator extends \Nette\Security\Permission {
    private function __construct(ACLRepository $service) {

        $roles=$service->getRoles();
        $resources=$service->getResources();
        $rules=$service->getRules();
        //dump($roles, $resources, $rules);

        foreach($roles as $role) {

            $this->addRole($role['name'], ($role->parent!= null?$role->parent->name:null));
        }

        foreach ($resources as $resource) {
            $this->addResource($resource['name']);
        }

        foreach ($rules as $rule) {
            if($rule['allowed']) {
                $this->allow($rule->role->name, $rule->resource->name, ($rule->privilege != null?$rule->privilege->name:null));
            }
            else {
                $this->deny($rule['role'], $rule['resource'], $rule['privilege']);
            }
        }
    }

    /**
     *
     * @param ACLRepository $service
     * @param Cache $cache
     * @return Authorizator
     */
    public static function getInstance(Service $service, \Nette\Caching\IStorage $storage) {
        $cache = new Cache($storage, 'Acl');
        if(true /*!$cache['acl']*/) {
            $instance=new self($service);
            $cache->save('acl', $instance,
                array(
                    Cache::TAGS=>array(
                        'Acl',
                    )
                ));
            return $instance;
        }
        return $cache['acl'];

    }
}
