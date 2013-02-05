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
}
