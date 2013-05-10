<?php
namespace Maps\Model\User;
/**
 * @Entity
 * @Table(name="user")
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class User extends \Maps\Model\BaseEntity {
    /**
     * @var string
     * @Column(type="string", length=200)
     */
    private $name;
    /**
     * @var string
     * @Column(type="string", length=100)
     */
    private $username;
    /** @var string
     * @Column(type="string", length=100)
     */
    private $mail;
    /** @var string
     * @Column(type="string", length=10)
     */
    private $role;

    /**
     * @param string $mail
     */
    public function setMail($mail) {
        $this->mail = $mail;
    }

    /**
     * @return string
     */
    public function getMail() {
        return $this->mail;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }


}
