<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 28.2.13
 * Time: 18:59
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Maps\Model\BaseEntity;

/**
 * Class Plan
 * @package Maps\Model\Floor
 * @Entity
 * @Table(name="floor_plans")
 */

class Plan extends BaseEntity{
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", inversedBy="nodes")
     * @JoinColumn(name="floor_id", referencedColumnName="id")
     */
    private $floor;
    /** @Column(type="integer") */
    private $revision = 1;
    /** @Column(type="boolean") */
    private $published = false;

    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     */
    private $user;
}