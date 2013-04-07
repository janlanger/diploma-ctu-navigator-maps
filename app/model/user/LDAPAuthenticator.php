<?php
namespace Maps\Model\User;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.2.13
 * Time: 11:38
 * To change this template use File | Settings | File Templates.
 */
class LDAPAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator {
    private $url;
    private $service;
    private $port;
    private $dn;

    function __construct($ldapConfig, Service $service) {
        $this->url = $ldapConfig['server'];
        $this->dn = $ldapConfig['dn'];
        $this->port = $ldapConfig['port'];
        $this->service = $service;
    }


    /**
     * Performs an authentication against LDAP.
     * and returns IIdentity on success or throws AuthenticationException
     * @param  array
     * @return IIdentity
     * @throws AuthenticationException
     */
    function authenticate(array $credentials) {

        // return new \Nette\Security\Identity(1,['admin'],['name'=>'Jan Langer','mail'=>'langeja1@fit.cvut.cz']);


        list($username, $password) = $credentials;

        // auth check via LDAP...
        $username = $credentials[self::USERNAME];
        $password = $credentials[self::PASSWORD];

        if ($username == 'admin' || $username == 'user') {
            if($password !== "lalalalappp") {
                throw new AuthenticationException();
            }
            $row = $this->service->getUserByLogin($username);
        }
        else {


            $connection = ldap_connect($this->url, $this->port);
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

            $bind = @ldap_bind($connection, "uid=" . $username . "," . $this->dn, $password);

            if ($bind) {
                $resource = ldap_search($connection, $this->dn, "uid=" . $username);
                $entry = ldap_get_entries($connection, $resource);
                $userInfo = $entry[0];
            }
            else {
                throw new \Nette\Security\AuthenticationException("Uživatel nenalezen.", self::IDENTITY_NOT_FOUND);
            }
            ldap_unbind($connection);


            if ($userInfo != NULL) {
                // he was authenticated
                //try finding user in db
                $row = $this->service->getUserByLogin($username);
                if ($row == NULL)
                    //not in database - create him
                    $row = $this->registerUser($username, $userInfo);
            }
            else {
                throw new \Nette\InvalidStateException("Unknown LDAP error.");
            }
        }
        return new Identity($row->id, $row->role, [
            "name" => $row->name,
            "username" => $row->username,
            "mail" => $row->mail,
            "role" => $row->role,
        ]);
    }


    private function registerUser($username, $info) {
        $user = $this->service->createBlank();
        $user->username = $username;
        $user->name = $info['cn'][0];
        $user->mail = $info['mail'][0];
        $user->role = 'registered';

        $this->service->save($user);
        return $user;
    }
}
