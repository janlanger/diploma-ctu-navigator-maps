<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 15.4.13
 * Time: 15:27
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Presenter;


use Maps\Model\Building\Building;
use Maps\Model\Building\Queries\BuildingWithFloors;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Floor\Floor;
use Maps\Model\Floor\Plan;
use Maps\Model\Metadata\Node;
use Maps\Model\Metadata\Path;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\SingleNode;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;

class ApiPresenter extends BasePresenter {

    /**
     * @var string
     * @persistent
     */
    public $apikey = NULL;


    protected function startup() {
        parent::startup();

        $allowedKeys = [];
        if (isset($this->context->parameters['api'])) {
            $allowedKeys = $this->context->parameters['api']['keys'];
        }
        if ($this->apikey == NULL || !in_array($this->apikey, $allowedKeys)) {
            $this->getHttpResponse()->setCode(401);
            $response = new TextResponse("API key was not included or it is invalid.");
            $this->sendResponse($response);
            $this->terminate();
        }
    }

    private function handleLastModification($lastModified) {
        if(!$this->getHttpContext()->isModified($lastModified)) {
            $this->getHttpResponse()->setHeader('Content-length', 0);
            $this->terminate();
        }
    }

    private function badRequest($msg) {
        $this->getHttpResponse()->setCode(400);
        $this->sendResponse(new TextResponse($msg));
    }



    public function actionBuilding($id = NULL) {
        if($id != NULL && ((int) $id) <= 0) {
            $this->badRequest("Invalid Building ID");
        }
        $result = $this->getRepository('building')->fetch(new BuildingWithFloors($id));
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

            $plans = $this->getRepository('plan')->fetch(new ActivePlanQuery($floorIds));
            $planIds = [];

            foreach ($plans as $plan) {
                if ($plan->getPublishedDate() > $lastUpdate) {
                    $lastUpdate = $plan->getPublishedDate();
                }
                $planIds[$plan->floor->id] = $plan->id;
            }
            $this->handleLastModification($lastUpdate);

            /** @var $item Building */
            foreach ($result as $item) {
                $payload[] = $this->getBuildingPayload($item, $planIds);
            }

            $this->sendResponse(new JsonResponse($payload));
        } else {
            throw new BadRequestException("Building #$id does not exists.", 404);
        }
    }

    public function actionFloor($id) {
        if ($id == NULL) {
            $this->badRequest("Floor collection resource is not supported.");
        }
        if(((int) $id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $floor = $this->getRepository('floor')->find($id);
        if($floor) {
            $lastUpdate = $floor->getLastUpdate();

            $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($floor->id));

            if($plan != NULL) {
                if($plan->getPublishedDate() > $lastUpdate) {
                    $lastUpdate = $plan->getPublishedDate();
                }
            }

            $this->handleLastModification($lastUpdate);

            $payload = $this->getFloorPayload($floor, [$floor->id => $plan->id]);
            $this->sendResponse(new JsonResponse($payload));
        } else {
            throw new BadRequestException("Unknown Floor ID", 404);
        }
    }

    public function actionFloorPlan($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($id));

        if ($plan != NULL) {
            $this->handleLastModification($plan->getPublishedDate());
            $this->sendResponse(new JsonResponse($this->getPlanPayload($plan)));
        }
        else {
            throw new BadRequestException("Plan for floor $id does not exists.", 404);
        }
    }

    public function actionFloorMetadata($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $metadata = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($id));

        if ($metadata != NULL) {
            $this->handleLastModification($metadata->getPublishedDate());
            $nodes = [];
            /** @var $node Node */
            foreach ($metadata->getNodes() as $node) {
                $nodes[] = $this->getNodePayload($node, $id);
            }
            $paths = [];
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
            throw new BadRequestException("Floor $id does not have any metadata.", 404);
        }
    }

    public function actionNode($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid node ID");
        }

        $node = $this->getRepository('meta_node')->fetchOne(new SingleNode($id));
        if($node != NULL) {
            $this->sendResponse(new JsonResponse($this->getNodePayload($node)));
        }
        else {
            throw new BadRequestException("Node with id $id does not exists.", 404);
        }
    }

    public function actionFloorNodes($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid floor ID");
        }

        $metadata = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($id));
        if($metadata != NULL) {
            $this->handleLastModification($metadata->getPublishedDate());
            $nodes = [];
            /** @var $node Node */
            foreach ($metadata->getNodes() as $node) {
                $nodes[] = $this->getNodePayload($node, $id);
            }
            $this->sendResponse(new JsonResponse($nodes));
        }
        else {
            throw new BadRequestException("Floor with id $id does not exists or has no metadata.", 404);
        }
    }

    public function actionPlan($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid plan ID");
        }

        $plan = $this->getRepository('plan')->find($id);
        if($plan == NULL || !$plan->getPublished()) {
            throw new BadRequestException("Plan with id $id does not exists.", 404);
        }
        $this->handleLastModification($plan->getPublishedDate());
        $this->sendResponse(new JsonResponse($this->getPlanPayload($plan)));
    }

    /**
     * @deprecated
     */
    public function actionPlan_v1($id) {
        if ($id == NULL || ((int)$id) <= 0) {
            $this->badRequest("Invalid building ID");
        }

        $building = $this->getRepository('building')->fetchOne(new BuildingWithFloors($id));
        if($building == NULL) {
            throw new BadRequestException("Building #$id does not exists.", 404);
        }
        $floorIds = [];
        foreach ($building->getFloors() as $floor) {
            $floorIds[] = $floor->id;
        }

        $metadata = $this->getRepository('meta_revision')->fetchAssoc(new ActiveRevision($floorIds), 'floor');
        $floors = [];
        foreach ($building->getFloors() as $floor) {
            $floors[] = $this->getFloorPayload($floor, [$floor->id => $building->id], isset($metadata[$floor->id])?$metadata[$floor->id]->nodes:NULL);
        }

        $tilesUrl = NULL;
        $plan = NULL;

        if(!empty($floorIds)) {
            $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($floorIds));

            $tilesUrl = $this->getContext()->tiles->getTilesBasePath($plan);
            $tilesUrl = substr($tilesUrl,0, strrpos($tilesUrl, "/"))."/";
        }

        $payload = [
            'id' => $building->id,
            'name' => $building->name,
            'tiles' => ($tilesUrl != NULL ? $this->getHttpRequest()->getUrl()->getBaseUrl().$tilesUrl : NULL),
            'maxZoom' => ($plan != NULL ? $plan->getMaxZoom() : NULL),
            'minZoom' => ($plan != NULL ? $plan->getMinZoom() : NULL),
            'floors' => $floors
        ];
        $this->sendResponse(new JsonResponse($payload));
    }

    /********** DATA CONVERSIONS **********/

    private function convertCoordinates($c) {
        $c = explode(',', $c);
        return [
            'latitude' => (float)$c[0],
            'longitude' => (float)$c[1]
        ];
    }

    private function getBuildingPayload(Building $item, $plans = []) {
        $floors = [];
        foreach ($item->getFloors() as $floor) {
            $floors[] = $this->getFloorPayload($floor, $plans);
        }

        usort($floors, function ($a, $b) {
            if ($a['floor'] < $b['floor']) return -1;
            if ($a['floor'] > $b['floor']) return 1;
            return 0;
        });


        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'type' => 'building',
            'coordinates' => $this->convertCoordinates($item->getGpsCoordinates()),
            'floorNumber' => $item->getFloorCount(),
            'minFloor' => $floors[0]['floor'],
            'floors' => $floors,
        ];
    }

    private function getFloorPayload(Floor $floor, $plans = [], $nodesData = NULL) {
        $nodes = [];
        if($nodesData != null) {
            foreach($nodesData as $node) {
                $nodes[] = $this->getNodePayload($node, $floor->id);
            }
        }
        $r = [
            'id' => $floor->id,
            'floorName' => $floor->getName(),
            'floor' => $floor->getFloorNumber(),
            'floorHeight' => $floor->getFloorHeight(),
            'plan' => isset($plans[$floor->id]) ? $plans[$floor->id] : NULL,
        ];
        if(!empty($nodes)) {
            $r['nodes'] = $nodes;
        }
        return $r;
    }

    private function getPlanPayload(Plan $plan) {
        return [
            'id' => $plan->id,
            'tiles' => $this->getHttpRequest()->getUrl()->getBaseUrl() . $this->context->tiles->getTilesBasePath($plan),
            'minZoom' => $plan->getMinZoom(),
            'maxZoom' => $plan->getMaxZoom(),
            'boundingSW' => $this->convertCoordinates($plan->getBoundingSW()),
            'boundingNE' => $this->convertCoordinates($plan->getBoundingNE()),
            'floor' => $plan->floor->id,
        ];
    }

    private function getNodePayload(Node $node, $floorId = NULL) {
        $p = $node->getProperties();
        return [
            'id' => $p->id,
            'type' => $p->getType(),
            'name' => is_null($p->getName())?$p->getRoom():$p->getName().(is_null($p->getRoom())?" - ".$p->getRoom():""),
            'room' => $p->getRoom(),
            'fromFloor' => $p->getFromFloor(),
            'toFloor' => ($p->getToFloor() != NULL ? $p->getToFloor()->id : NULL),
            'coordinates' => $this->convertCoordinates($p->getGpsCoordinates()),
            'floor' => (is_null($floorId) ? $node->getRevision()->getFloor()->id : $floorId),
        ];
    }

    private function getPathPayload(Path $path, $floorId = NULL) {
        $p = $path->getProperties();
        return [
            'id' => $p->id,
            'start' => $p->getStartNode()->id,
            'end' => $p->getEndNode()->id,
            'length' => $path->getLength(),
            'isFloorExchangePoint' => $p->isFloorExchange(),
            'destinationFloor' => ($p->getDestinationFloor() != NULL ? $p->getDestinationFloor()->id : NULL),
        ];
    }


}