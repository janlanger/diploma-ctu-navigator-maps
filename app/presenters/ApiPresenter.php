<?php

namespace Maps\Presenter;


use Maps\Model\Building\Building;
use Maps\Model\Building\Queries\BuildingWithFloors;
use Maps\Model\Floor\Queries\ActivePlanQuery;
use Maps\Model\Floor\Floor;
use Maps\Model\Floor\Plan;
use Maps\Model\Metadata\FloorConnection;
use Maps\Model\Metadata\Node;
use Maps\Model\Metadata\Path;
use Maps\Model\Metadata\Queries\ActiveFloorConnections;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\NodeByRoom;
use Maps\Model\Metadata\Queries\SingleNode;
use Maps\Tools\JsonErrorResponse;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;

/**
 * Class ApiPresenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class ApiPresenter extends BasePresenter {

    /**
     * @var string
     * @persistent
     */
    public $apikey = NULL;

    /** {@inheritdoc} */
    protected function startup() {
        parent::startup();

        $allowedKeys = [];
        if (isset($this->context->parameters['api'])) {
            $allowedKeys = $this->context->parameters['api']['keys'];
        }
        if ($this->apikey == NULL || !isset($allowedKeys[$this->apikey])) {
            $this->getHttpResponse()->setCode(401);
            $response = new JsonErrorResponse("API key was not included or it is invalid.");
            $this->sendResponse($response);
            $this->terminate();
        }
    }

    /**
     * Check last modification header and terminates presenter if provided date is less or equal
     * @param \DateTime $lastModified
     */
    private function handleLastModification($lastModified) {
        if(!$this->getHttpContext()->isModified($lastModified)) {
            $this->getHttpResponse()->setHeader('Content-length', 0);
            $this->terminate();
        }
    }

    /**
     * Handles HTTP 400 Bad Request
     * @param string $msg error message
     */
    private function badRequest($msg) {
        $this->getHttpResponse()->setCode(400);
        $this->sendResponse(new JsonErrorResponse($msg));
    }

    /**
     * Handles HTTP 404 Not Found
     *
     * @param string $msg error message
     */
    private function notFound($msg) {
        $this->getHttpResponse()->setCode(404);
        $this->sendResponse(new JsonErrorResponse($msg));
    }

    private function stripNulls($data) {
        return array_filter($data);
    }

    /**
     * @param int|null $id building id
     */
    public function actionBuilding($id = NULL) {
        if($id != NULL && ((int) $id) <= 0) {
            $this->badRequest("Invalid Building ID");
        }
        $result = $this->context->buildingRepository->fetch(new BuildingWithFloors($id));
        $payload = [];

        if(count($result)) {
            $lastUpdate = new \DateTime("2000-01-01");

            $floorIds = [];
            foreach ($result as $item) {
                if($item->getLastUpdate() > $lastUpdate) {
                    $lastUpdate = $item->getLastUpdate();
                }
                foreach ($item->getFloors() as $floor) {
                    if($floor->getLastUpdate() > $lastUpdate) {
                        $lastUpdate = $floor->getLastUpdate();
                    }
                    $floorIds[] = $floor->id;
                }
            }
            $this->handleLastModification($lastUpdate);

            /** @var $item Building */
            foreach ($result as $item) {
                $payload[] = $this->getBuildingPayload($item);
            }

            $this->sendResponse(new JsonResponse($payload));
        } else {
            $this->notFound("Building #$id does not exists.");
        }
    }

    /**
     * @param int|null $id building id
     */
    public function actionBuilding_v1($id) {
        if ($id != NULL && ((int)$id) <= 0) {
            $this->badRequest("Invalid Building ID");
        }
        if($id != NULL) {
            $result = [$this->context->buildingRepository->find($id)];
        }
        else {
            $result = $this->context->buildingRepository->findAll();
        }
        $payload = [];

        if (count($result)) {
            $lastUpdate = new \DateTime("2000-01-01");

            $floorIds = [];
            foreach ($result as $item) {
                if ($item->getLastUpdate() > $lastUpdate) {
                    $lastUpdate = $item->getLastUpdate();
                }
            }
            $this->handleLastModification($lastUpdate);

            /** @var $item Building */
            foreach ($result as $item) {
                $payload[] = $this->getBuildingPayload($item, TRUE);
            }
            if(count($payload) == 1) {
                $payload = array_shift($payload);
            }

            $this->sendResponse(new JsonResponse($payload));
        }
        else {
            $this->notFound("Building #$id does not exists.");
        }
    }

    /**
     * @param int $id floor ID
     */
    public function actionFloor($id) {
        if ($id == NULL) {
            $this->badRequest("Floor collection resource is not supported.");
        }
        if(((int) $id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $floor = $this->context->floorRepository->find($id);
        if($floor) {
            $lastUpdate = $floor->getLastUpdate();

            $this->handleLastModification($lastUpdate);

            $payload = $this->getFloorPayload($floor);
            $this->sendResponse(new JsonResponse($payload));
        } else {
            $this->notFound("Unknown Floor ID");
        }
    }

    /**
     * @param int $id floor id
     */
    public function actionFloorPlan($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $plan = $this->context->planRepository->fetchOne(new ActivePlanQuery($id));

        if ($plan != NULL) {
            $this->handleLastModification($plan->getPublishedDate());
            $this->sendResponse(new JsonResponse($this->getPlanPayload($plan)));
        }
        else {
            $this->notFound("Plan for floor $id does not exists.");
        }
    }

    /**
     * @param int $id floor id
     */
    public function actionFloorMetadata($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $metadata = $this->context->metadataRevisionRepository->fetchOne(new ActiveRevision($id));

        if ($metadata != NULL) {

            $lastModified = $metadata->getPublishedDate();

            $floorConnections = $this->context->floorConnectionRepository->fetch(new ActiveFloorConnections($metadata));

            $paths = [];
            /** @var $connection FloorConnection */
            foreach ($floorConnections as $connection) {
                if ($connection->getCreated() > $lastModified) {
                    $lastModified = $connection->getCreated();
                }
                $paths[] = $this->getFloorConnectionPayload($connection);
            }
            $this->handleLastModification($lastModified);


            $nodes = [];
            /** @var $node Node */
            foreach ($metadata->getNodes() as $node) {
                $nodes[] = $this->getNodePayload($node, $id, $metadata->getFloor()->getBuilding()->id);
            }

            /** @var $path Path */
            foreach ($metadata->getPaths() as $path) {
                $paths[] = $this->getPathPayload($path, $id);
            }

            $payload = [
                'nodes' => $nodes,
                'paths' => $paths
            ];
            $this->sendResponse(new JsonResponse($payload));
        }
        else {
            $this->notFound("Floor $id does not have any metadata.");
        }
    }

    /**
     * @param int $id node id
     */
    public function actionNode($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid node ID");
        }

        $node = $this->context->nodeRepository->fetchOne(new SingleNode($id));
        if($node != NULL) {
            $this->sendResponse(new JsonResponse($this->getNodePayload($node)));
        }
        else {
            $this->notFound("Node with id $id does not exists.");
        }
    }

    public function actionRoomNode($id) {
        if($id == NULL) {
            $this->badRequest("Invalid room number");
        }

        $node = $this->context->nodeRepository->fetchOne(new NodeByRoom($id));
        if($node == null) {
            $this->notFound("Node for your room was not found.");
        }

        $this->sendResponse(new JsonResponse($this->getNodePayload($node)));

    }

    /**
     * @param int $id floor ID
     */
    public function actionFloorNodes($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $metadata = $this->context->metadataRevisionRepository->fetchOne(new ActiveRevision($id));
        if($metadata != NULL) {
            $this->handleLastModification($metadata->getPublishedDate());
            $nodes = [];
            /** @var $node Node */
            foreach ($metadata->getNodes() as $node) {
                $nodes[] = $this->getNodePayload($node, (int) $id, $metadata->getFloor()->getBuilding()->id);
            }
            $this->sendResponse(new JsonResponse($nodes));
        }
        else {
            $this->notFound("Floor with id $id does not exists or has no metadata.");
        }
    }

    /**
     * @deprecated
     * @param int $id building ID
     */
    public function actionPlan_v1($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid building ID");
        }

        $building = $this->context->buildingRepository->fetchOne(new BuildingWithFloors($id));
        if($building == NULL) {
            $this->notFound("Building #$id does not exists.");
        }
        $floorIds = [];
        foreach ($building->getFloors() as $floor) {
            $floorIds[] = $floor->id;
        }

        $metadata = $this->context->metadataRevisionRepository->fetchAssoc(new ActiveRevision($floorIds), 'floor');
        $floors = [];
        foreach ($building->getFloors() as $floor) {
            $floors[] = $this->getFloorPayload($floor, [$floor->id => $building->id], isset($metadata[$floor->id])?$metadata[$floor->id]->nodes:NULL);
        }

        $tilesUrl = NULL;
        $plan = NULL;

        if(!empty($floorIds)) {
            $plan = $this->context->planRepository->fetchOne(new ActivePlanQuery($floorIds));

            $tilesUrl = $this->getContext()->tiles->getTilesBasePath($plan);
            $tilesUrl = substr($tilesUrl,0, strrpos($tilesUrl, "/"))."/";
        }

        $payload = $this->stripNulls([
            'id' => $building->id,
            'name' => $building->name,
            'tiles' => ($tilesUrl != NULL ? $this->getHttpRequest()->getUrl()->getBaseUrl().$tilesUrl : NULL),
            'maxZoom' => ($plan != NULL ? $plan->getMaxZoom() : NULL),
            'minZoom' => ($plan != NULL ? $plan->getMinZoom() : NULL),
            'floors' => $floors
        ]);
        $this->sendResponse(new JsonResponse($payload));
    }

    /********** DATA CONVERSIONS **********/
    /**
     * @param string $c GPS coordinates string representation
     * @return array associative latitude, longitude
     */
    private function convertCoordinates($c) {
        $c = explode(',', $c);
        return [
            'latitude' => (float)$c[0],
            'longitude' => (float)$c[1]
        ];
    }

    /**
     * Generates building response payload
     *
     * @param Building $item
     * @param bool $version1 include floors to response?
     * @return array
     */
    private function getBuildingPayload(Building $item, $version1 = FALSE) {
        $r = $this->stripNulls([
            'id' => $item->getId(),
            'name' => $item->getName(),
            'type' => 'building',
            'coordinates' => $this->convertCoordinates($item->getGpsCoordinates()),
            'floorNumber' => $item->getFloorCount(),
            'address' => $item->getAddress(),
            //'minFloor' => $floors[0]['floor'],
        ]);

        if($version1) {
            $r['plan'] = $item->getId();
        }

        if(!$version1) {
            $floors = [];
            foreach ($item->getFloors() as $floor) {
                $floors[] = $this->stripNulls($this->getFloorPayload($floor));
            }

            usort($floors, function ($a, $b) {
                if ($a['floor'] < $b['floor']) return -1;
                if ($a['floor'] > $b['floor']) return 1;
                return 0;
            });
            $r['floors'] = $floors;
        }
        return $r;
    }

    /**
     * Generates floor payload
     *
     * @param Floor $floor
     * @param array $plans
     * @param array $nodesData
     * @return array
     */
    private function getFloorPayload(Floor $floor, $plans = [], $nodesData = NULL) {
        $nodes = [];
        if($nodesData != NULL) {
            foreach($nodesData as $node) {
                $nodes[] = $this->getNodePayload($node, $floor->id, $floor->getBuilding()->id);
            }
        }
        $r = $this->stripNulls([
            'id' => $floor->id,
            'floorName' => $floor->getName(),
            'floor' => $floor->getFloorNumber(),
            'height' => $floor->getFloorHeight(),
            'building' => $floor->getBuilding()->id,
        ]);
        if(!empty($nodes)) {
            $r['nodes'] = $this->stripNulls($nodes);
        }
        return $r;
    }

    /**
     * Generates floor plan payload
     * @param Plan $plan
     * @return array
     */
    private function getPlanPayload(Plan $plan) {
        return $this->stripNulls([
            'id' => $plan->id,
            'tiles' => $this->getHttpRequest()->getUrl()->getBaseUrl() . $this->context->tiles->getTilesBasePath($plan),
            'minZoom' => $plan->getMinZoom(),
            'maxZoom' => $plan->getMaxZoom(),
            'boundingSW' => $this->convertCoordinates($plan->getBoundingSW()),
            'boundingNE' => $this->convertCoordinates($plan->getBoundingNE()),
            'floor' => $plan->floor->getFloorNumber(),
        ]);
    }

    /**
     * Generates node response payload
     *
     * @param Node $node
     * @param int|null $floorId
     * @return array
     */
    private function getNodePayload(Node $node, $floorId = NULL, $buildingId = null) {
        $p = $node->getProperties();
        return $this->stripNulls([
            'id' => $p->id,
            'type' => $p->getType(),
            'name' => is_null($p->getName())?$p->getRoom():$p->getName().(is_null($p->getRoom())?" - ".$p->getRoom():""),
            'room' => $p->getRoom(),
            'fromFloor' => $p->getFromFloor(),
            'toFloor' => ($p->getToFloor() != NULL ? $p->getToFloor()->id : NULL),
            'coordinates' => $this->convertCoordinates($p->getGpsCoordinates()),
            'floor' => ((int) (is_null($floorId) ? $node->getRevision()->getFloor()->id : $floorId)),
            'building' => (int) (is_null($buildingId) ? $node->getRevision()->getFloor()->getBuilding()->id : $buildingId),
        ]);
    }

    /**
     * @param Path $path
     * @param int|null $floorId
     * @return array
     */
    private function getPathPayload(Path $path, $floorId = NULL) {
        $p = $path->getProperties();
        return $this->stripNulls([
            'id' => $p->id,
            'start' => $p->getStartNode()->id,
            'end' => $p->getEndNode()->id,
            'length' => $path->getLength(),
            'isFloorConnection' => FALSE,
        ]);
    }

    /**
     * @param FloorConnection $path
     * @return array
     */
    private function getFloorConnectionPayload(FloorConnection $path) {
        return $this->stripNulls([
            'id' => $path->id,
            'start' => $path->getNodeOne()->id,
            'end' => $path->getNodeTwo()->id,
            'length' => $path->estimatedLength(),
            'isFloorConnection' => TRUE,
            'destinationFloor' => $path->getRevisionTwo()->getFloor()->getId(),
            'type'=>$path->getType(),
        ]);
    }


}