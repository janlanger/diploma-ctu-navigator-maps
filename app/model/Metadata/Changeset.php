<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 13.3.13
 * Time: 19:25
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Model\BaseEntity;
use Maps\Model\User\User;

/**
 * Class Changeset
 * @package Maps\Model\Metadata
 * @Entity
 * @table("metadata_changesets")
 */
class Changeset extends BaseEntity {

    const STATE_NEW = 'new';
    const STATE_APPROVED = 'approved';
    const STATE_REJECTED = 'rejected';

    /**
     * @var User
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     * @JoinColumn(name="submitted_by", referencedColumnName="id", nullable=false)
     */
    private $submitted_by;
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision")
     * @JoinColumn(name="against_revision", referencedColumnName="id", nullable=false)
     */
    private $against_revision;
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $submitted_date;

    /**
     * @var string
     * @Column(type="string", length=20)
     */
    private $state;

    /**
     * @var User
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     */
    private $processed_by;
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $processed_date;
    /**
     * @var Revision
     * @ManyToOne(targetEntity="Revision")
     */
    private $in_revision;
    /**
     * @var string
     * @Column(type="string", length=50)
     */
    private $comment;

}