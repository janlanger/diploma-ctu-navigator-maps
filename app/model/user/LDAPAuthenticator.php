<?php
namespace Maps\Model\User;
use Nette\Security\Identity;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.2.13
 * Time: 11:38
 * To change this template use File | Settings | File Templates.
 */
class LDAPAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{
    private $url;
    private $service;
    function __construct($serverUrl, Service $service) {
        $this->url = $serverUrl;
        $this->service = $service;
    }


    /**
     * Performs an authentication against LDAP.
     * and returns IIdentity on success or throws AuthenticationException
     * @param  array
     * @return IIdentity
     * @throws AuthenticationException
     */
    function authenticate(array $credentials)
    {
       // return new \Nette\Security\Identity(1,['admin'],['name'=>'Jan Langer','mail'=>'langeja1@fit.cvut.cz']);



        list($username, $password) = $credentials;

        // auth check via LDAP...
        /*  $username = $credentials[self::USERNAME];
          $password = $credentials[self::PASSWORD];
          $connection = ldap_connect($this->url);
          $bind = ldap_bind($connection, $username, $password);*/

        //TODO create new user in DB if not exists

        $row = $this->service->getUserByLogin($username);




        return new Identity($row->id, $row->role, [
            "name"=>$row->name,
            "username"=>$row->username,
            "mail"=>$row->mail,
            "role"=>$row->role,
        ]);
    }
}
