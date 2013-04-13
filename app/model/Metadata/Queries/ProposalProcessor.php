<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 19.3.13
 * Time: 22:10
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Maps\Components\Forms\Form;
use Maps\Model\Dao;
use Maps\Model\Metadata\Queries\NodePropertiesQuery;
use Maps\Model\Metadata\Queries\PathPropertiesByNodes;
use Maps\Model\User\User;
use Maps\Tools\Mixed;

class ProposalProcessor {

    /** @var Revision */
    private $actualRevision = NULL;

    private $nodePropertiesRepository;
    /** @var \Maps\Model\Dao */
    private $pathPropertiesRepository;
    /** @var Dao */
    private $changesetRepository;
    /** @var Dao */
    private $nodeChangeRepository;

    private $user;

    private $changeset;


    function __construct($actualRevision, User $user,
                         Dao $nodeProperties, Dao $pathProperties,
                         Dao $changeset, Dao $nodeChange, Dao $pathChange) {
        $this->actualRevision = $actualRevision;
        $this->nodePropertiesRepository = $nodeProperties;
        $this->pathPropertiesRepository = $pathProperties;
        $this->changesetRepository = $changeset;
        $this->nodeChangeRepository = $nodeChange;
        $this->pathChangeRepository = $pathChange;
        $this->user = $user;
    }


    public function handle(Form $form) {
        if (!$form->isValid()) {
            return;
        }


        $values = $form->getValues();
        $changes = json_decode($values['definition'], TRUE);

        //remove null shitty things...

        foreach ($changes as $part => $p) {
            foreach ($p as $key => $section) {
                foreach ($section as $id => $node) {
                    if ($node == NULL) {
                        unset($changes[$part][$key][$id]);
                    }
                }
            }
        }

        if ($this->hasChanges($changes)) {

            /** @var $changeset Changeset */
            $this->changeset = $this->changesetRepository->createNew(NULL, [
                'state' => Changeset::STATE_NEW,
                'againstRevision' => $this->actualRevision,
                'comment' => $values['comment'],
                'submittedBy' => $this->user,
            ]);

            $this->processChanges($changes);

            $this->nodeChangeRepository->getEntityManager()->flush();
        }
    }

    private function processChanges($changes) {

        //load needed nodes properties for relations

        $nodePropertiesIds = [];

        foreach ($changes['nodes']['changed'] as $node) {
            $nodePropertiesIds[] = $node['propertyId'];
        }
        $nodePropertiesIds = array_merge($nodePropertiesIds, $changes['nodes']['deleted']);


        foreach ($changes['paths']['added'] as $path) {
            if (isset($path['start']['propertyId'])) {
                $nodePropertiesIds[] = $path['start']['propertyId'];
            }
            if (isset($path['end']['propertyId'])) {
                $nodePropertiesIds[] = $path['end']['propertyId'];
            }
        }

        $pathNodeIds = [];

        foreach ($changes['paths']['deleted'] as $path) {
            $nodePropertiesIds[] = $path['start'];
            $nodePropertiesIds[] = $path['end'];

            $pathNodeIds[] = [$path['start'], $path['end']];
        }

        $nodePropertiesIds = array_unique($nodePropertiesIds, SORT_NUMERIC);

        if (count($nodePropertiesIds) > 0) {
            $dbNodeProperties = $this->nodePropertiesRepository->fetchAssoc(new NodePropertiesQuery($nodePropertiesIds), 'id');
        }
        else {
            $dbNodeProperties = [];
        }

        $dbPathProperties = $this->pathPropertiesRepository->fetchAssoc(new PathPropertiesByNodes($pathNodeIds), 'id');

        //load end


        $nodesAdd = [];

        $nodes = [];

        foreach ($changes['nodes']['added'] as $id => $node) {
            $nodesAdd[$id] = $this->nodePropertiesRepository->createNew(NULL, [
                'gpsCoordinates' => $node['position'],
                'type' => $node['type'],
                'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : NULL),
                'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : NULL),
            ]);

            $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                'changeset' => $this->changeset,
                'properties' => $nodesAdd[$id],
                'original' => NULL,
                'wasDeleted' => FALSE
            ]);
        }


        foreach ($changes['nodes']['changed'] as $node) {
            $item = $this->nodePropertiesRepository->createNew(NULL, [
                'gpsCoordinates' => $node['position'],
                'type' => $node['type'],
                'name' => (isset($node['name']) && trim($node['name']) != "" ? $node['name'] : NULL),
                'room' => (isset($node['room']) && trim($node['room']) != "" ? $node['room'] : NULL),
            ]);

            $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                'changeset' => $this->changeset,
                'properties' => $item,
                'original' => $dbNodeProperties[$node['propertyId']],
                'wasDeleted' => FALSE
            ]);
        }


        foreach ($changes['nodes']['deleted'] as $nodeId) {
            $nodes[] = $this->nodeChangeRepository->createNew(NULL, [
                'changeset' => $this->changeset,
                'properties' => NULL,
                'original' => $dbNodeProperties[$nodeId],
                'wasDeleted' => TRUE
            ]);
        }

        $paths = [];

        foreach ($changes['paths']['added'] as $path) {
            $item = $this->pathPropertiesRepository->createNew(NULL, [
                "startNode" => (isset($path['start']['propertyId']) ? $dbNodeProperties[$path['start']['propertyId']] : $nodesAdd[$path['start']['id']]),
                "endNode" => (isset($path['end']['propertyId']) ? $dbNodeProperties[$path['end']['propertyId']] : $nodesAdd[$path['end']['id']]),
            ]);

            $paths[] = $this->pathChangeRepository->createNew(NULL, [
                'changeset' => $this->changeset,
                'properties' => $item,
                'original' => NULL,
                'wasDeleted' => FALSE,
            ]);
        }

        $deletedPaths = [];

        foreach ($changes['paths']['deleted'] as $path) {
            foreach ($dbPathProperties as $p) {
                if ($p->startNode->id == $path['start'] && $p->endNode->id == $path['end']) {
                    break;
                }
            }

            $paths[] = $this->pathChangeRepository->createNew(NULL, [
                'changeset' => $this->changeset,
                'properties' => NULL,
                'original' => $p,
                'wasDeleted' => TRUE,
            ]);
        }

        $this->nodeChangeRepository->add($nodes);
        $this->pathChangeRepository->add($paths);
    }

    private function hasChanges($changes) {
        $nodes = $changes['nodes'];
        $paths = $changes['paths'];

        return ((isset($nodes['added']) && !empty($nodes['added'])) ||
                (isset($nodes['changed']) && !empty($nodes['changed'])) ||
                (isset($nodes['deleted']) && !empty($nodes['deleted'])) ||
                (isset($paths['added']) && !empty($paths['added'])) ||
                (isset($paths['deleted']) && !empty($paths['deleted']))
        );

    }

}