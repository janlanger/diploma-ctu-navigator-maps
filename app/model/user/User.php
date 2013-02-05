<?php
namespace Maps\Model\User;
/**
 * @Entity
 * @Table(name="user")
 */
class User extends \Maps\Model\BaseEntity {
    /** @var @Column(type="string", length=200) */
    private $name;
    /** @var @Column(type="string", length=100) */
    private $username;
    /** @var @Column(type="string", length=100) */
    private $mail;
    /** @var @Column(type="string", length=10) */
    private $role;

    /**
     * @param  $mail
     */
    public function setMail($mail) {
        $this->mail = $mail;
    }

    /**
     * @return
     */
    public function getMail() {
        return $this->mail;
    }

    /**
     * @param  $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param  $role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @return
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * @param  $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return
     */
    public function getUsername() {
        return $this->username;
    }


}
