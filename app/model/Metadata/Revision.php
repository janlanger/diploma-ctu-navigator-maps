<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:12
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;

/**
 * Class Revision
 * @package Maps\Model\Metadata
 * @Entity
 * @table(name="metadata_revision")
 */
class Revision extends BaseEntity {

    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor")
     * @JoinColumn(name="floor_id",referencedColumnName="id",onDelete="CASCADE")
     */
    private $floor;
    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @JoinColumn(name="user_id",referencedColumnName="id",onDelete="CASCADE")
     */
    private $user;
    /**
     * @Column(type="integer")
     */
    private $revision = 1;

    /** @Column(type="boolean") */
    private $published = false;
    /** @Column(type="datetime", nullable=true) */
    private $published_date;

}