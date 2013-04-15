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
use Maps\Model\Floor\ActivePlansOfFloors;
use Maps\Model\Floor\Floor;
use Maps\Model\Floor\Plan;
use Maps\Model\Metadata\Node;
use Maps\Model\Metadata\Path;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\SingleNode;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Environment;

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


    public function actionBuilding($id = NULL) {
        $result = $this->getRepository('building')->fetch(new BuildingWithFloors($id));
        $payload = [];

        $floorIds = [];
        foreach ($result as $item) {
            foreach ($item->getFloors() as $floor) {
                $floorIds[] = $floor->id;
            }
        }

        $plans = $this->getRepository('plan')->fetchPairs(new ActivePlansOfFloors($floorIds), 'floor', 'id');

        /** @var $item Building */
        foreach ($result as $item) {
            $payload[] = $this->getBuildingPayload($item, $plans);
        }

        $this->sendResponse(new JsonResponse($payload));
    }

    public function actionFloor($id) {
        if ($id == NULL) {
            $this->getHttpResponse()->setCode(400);
            $this->sendResponse(new TextResponse("Floor collection resource is not supported."));
        }
        $floor = $this->getRepository('floor')->find($id);
        $plan = $this->getRepository('plan')->fetchPairs(new ActivePlansOfFloors([$floor->id]), 'floor', 'id');

        $payload = $this->getFloorPayload($floor, $plan);
        $this->sendResponse(new JsonResponse($payload));
    }

    public function actionFloorPlan($id) {
        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($id));

        if ($plan != NULL) {
            $this->sendResponse(new JsonResponse($this->getPlanPayload($plan)));
        }
        else {
            throw new BadRequestException("Plan for floor $id does not exists.", 404);
        }
    }

    public function actionFloorMetadata($id) {
        $metadata = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($id));

        if ($metadata != NULL) {
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
        $node = $this->getRepository('meta_node')->fetchOne(new SingleNode($id));
        if($node != NULL) {
            $this->sendResponse(new JsonResponse($this->getNodePayload($node)));
        }
        else {
            throw new BadRequestException("Node with id $id does not exists.", 404);
        }
    }

    /********** DATA CONVERSIONS **********/

    private function convertCoordinates($c) {
        $c = explode(',', $c);
        return [
            'latitude' => (float)$c[0],
            'longtitude' => (float)$c[1]
        ];
    }

    private function getBuildingPayload(Building $item, $plans = []) {
        $floors = [];
        foreach ($item->getFloors() as $floor) {
            $floors[] = $this->getFloorPayload($floor, $plans);
        }
        $minFloor = 0;

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

    private function getFloorPayload(Floor $floor, $plans = []) {
        return [
            'id' => $floor->id,
            'floorName' => $floor->getName(),
            'floor' => $floor->getFloorNumber(),
            'floorHeight' => $floor->getFloorHeight(),
            'plan' => isset($plans[$floor->id]) ? $plans[$floor->id] : NULL,
        ];
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
            'name' => $p->getName(),
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