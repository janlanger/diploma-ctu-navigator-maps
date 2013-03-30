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
 * @Table(name="floor_plans",
 * indexes={
 * @Index(columns={"published"})
 * },
 * uniqueConstraints={
 * @UniqueConstraint(columns={"floor_id", "revision"})
 *  })
 *
 */

class Plan extends BaseEntity {
    /**
     * @ManyToOne(targetEntity="Maps\Model\Floor\Floor", inversedBy="nodes")
     * @JoinColumn(name="floor_id", referencedColumnName="id")
     */
    private $floor;
    /** @Column(type="integer") */
    private $revision = 1;
    /** @Column(type="boolean") */
    private $published = FALSE;

    /** @Column(type="boolean") */
    private $inPublishQueue = FALSE;

    /**
     * @ManyToOne(targetEntity="Maps\Model\User\User")
     */
    private $user;

    /** @Column(type="string",length=200) */
    private $sourceFile;

    /** @Column(type="integer", nullable=true) */
    private $sourceFilePage = 1;

    /** @Column(type="datetime") */
    private $added_date;
    /** @Column(type="datetime", nullable=true) */
    private $published_date;


    /** @Column(type="string",length=100, nullable=true) */
    private $reference_topLeft;
    /** @Column(type="string",length=100, nullable=true) */
    private $reference_topRight;
    /** @Column(type="string",length=100, nullable=true) */
    private $reference_bottomRight;

    /** @Column(type="string",length=100, nullable=true) */
    private $bounding_SW;
    /** @Column(type="string",length=100, nullable=true) */
    private $bounding_NE;
    /** @Column(type="integer", nullable=true) */
    private $maxZoom;
    /** @Column(type="integer", nullable=true) */
    private $minZoom;

    public function __construct() {
        $this->added_date = new \DateTime();
    }

    public function setFloor($floor) {
        $this->floor = $floor;
    }

    public function getFloor() {
        return $this->floor;
    }

    public function setPlan($plan) {
        $this->sourceFile = $plan;
    }

    public function getPlan() {
        return $this->sourceFile;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    public function setReferenceBottomRight($reference_bottomRight) {
        $this->reference_bottomRight = $reference_bottomRight;
    }

    public function getReferenceBottomRight() {
        return $this->reference_bottomRight;
    }

    public function setReferenceTopLeft($reference_topLeft) {
        $this->reference_topLeft = $reference_topLeft;
    }

    public function getReferenceTopLeft() {
        return $this->reference_topLeft;
    }

    public function setReferenceTopRight($reference_topRight) {
        $this->reference_topRight = $reference_topRight;
    }

    public function getReferenceTopRight() {
        return $this->reference_topRight;
    }

    public function setAddedDate($added_date) {
        $this->added_date = $added_date;
    }

    public function getAddedDate() {
        return $this->added_date;
    }

    public function setPublished($published) {
        $this->published = $published;
    }

    public function getPublished() {
        return $this->published;
    }

    public function setPublishedDate($published_date) {
        $this->published_date = $published_date;
    }

    public function getPublishedDate() {
        return $this->published_date;
    }

    public function setRevision($revision) {
        $this->revision = $revision;
    }

    public function getRevision() {
        return $this->revision;
    }

    public function setInPublishQueue($inPublishQueue) {
        $this->inPublishQueue = $inPublishQueue;
    }

    public function getInPublishQueue() {
        return $this->inPublishQueue;
    }

    public function setSourceFile($sourceFile) {
        $this->sourceFile = $sourceFile;
    }

    public function getSourceFile() {
        return $this->sourceFile;
    }

    public function setSourceFilePage($sourceFilePage) {
        $this->sourceFilePage = $sourceFilePage;
    }

    public function getSourceFilePage() {
        return $this->sourceFilePage;
    }

    public function setBoundingNE($bounding_NE) {
        $this->bounding_NE = $bounding_NE;
    }

    public function getBoundingNE() {
        return $this->bounding_NE;
    }

    public function setBoundingSW($bounding_SW) {
        $this->bounding_SW = $bounding_SW;
    }

    public function getBoundingSW() {
        return $this->bounding_SW;
    }

    public function setMaxZoom($maxZoom) {
        $this->maxZoom = $maxZoom;
    }

    public function getMaxZoom() {
        return $this->maxZoom;
    }

    public function setMinZoom($minZoom) {
        $this->minZoom = $minZoom;
    }

    public function getMinZoom() {
        return $this->minZoom;
    }
}