<?php
namespace Maps\Model\User;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\Identity;

/**
 * Class LDAPAuthenticator
 *
 * @package Maps\Model\User
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class LDAPAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator {
    /** @var  string LDAP url */
    private $url;
    /** @var \Maps\Model\User\Service  */
    private $service;
    /** @var int LDAP port */
    private $port;
    /** @var string LDAP DN */
    private $dn;

    /**
     * @param array $ldapConfig
     * @param Service $service
     */
    function __construct($ldapConfig, Service $service) {
        $this->url = $ldapConfig['server'];
        $this->dn = $ldapConfig['dn'];
        $this->port = $ldapConfig['port'];
        $this->service = $service;
    }


    /**
     * Performs an authentication against LDAP.
     * and returns IIdentity on success or throws AuthenticationException
     *
     * @param array $credentials
     * @throws \Nette\InvalidStateException
     * @throws \Nette\Security\AuthenticationException
     * @return IIdentity
     */
    function authenticate(array $credentials) {

        // return new \Nette\Security\Identity(1,['admin'],['name'=>'Jan Langer','mail'=>'langeja1@fit.cvut.cz']);


        list($username, $password) = $credentials;

        // auth check via LDAP...
        $username = $credentials[self::USERNAME];
        $password = $credentials[self::PASSWORD];

        if ($username == 'admin' || $username == 'user') {
            //remove in production
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
                if ($row == NULL) {
                    //not in database - create him
                    $row = $this->registerUser($username, $userInfo);
                } else {
                    //he was in db - update info
                    $this->setUserInfo($userInfo, $row);
                    $this->service->save($row);
                }
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

    /**
     * Creates and stores new user entity
     *
     * @param $username
     * @param $info
     * @return mixed
     */
    private function registerUser($username, $info) {
        $user = $this->service->createBlank();
        $user->username = $username;
        $this->setUserInfo($info, $user);
        $user->role = 'registered';

        $this->service->save($user);
        return $user;
    }

    /**
     *
     * @param $info
     * @param User $user
     */
    private function setUserInfo($info, User $user) {
        $user->mail = (isset($info['preferredemail']) && !empty($info['preferredemail'])) ? $info['preferredemail'][0] : $info['mail'][0];

        $given = $info['givenname'];
        unset($given['count']);
        $name = implode(" ", $given);
        $sn = $info['sn'];
        unset($sn['count']);
        $name .= " ".implode(" ", $sn);

        $user->setName($name);
    }
}
