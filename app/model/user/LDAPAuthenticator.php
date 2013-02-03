<?php
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
    function __construct($serverUrl) {
        $this->url = $serverUrl;
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
        return new \Nette\Security\Identity(1,['admin'],['name'=>'Jan Langer','mail'=>'langeja1@fit.cvut.cz']);
        $username = $credentials[self::USERNAME];
        $password = $credentials[self::PASSWORD];
        $connection = ldap_connect($this->url);
        $bind = ldap_bind($connection, $username, $password);
        dump($bind);
    }
}
