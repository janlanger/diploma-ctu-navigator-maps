<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.2.13
 * Time: 12:35
 * To change this template use File | Settings | File Templates.
 */
class ACLRepository extends Repository {

    public function getRoles(){
        return $this->connection->table("acl_roles");
    }
    public function getResources() {
        return $this->connection->table('acl_resources');
    }
    public function getRules() {
        return $this->connection->table('acl');
    }

}
