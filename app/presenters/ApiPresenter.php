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
use Maps\Model\Floor\Floor;
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
        if(isset($this->context->parameters['api'])) {
            $allowedKeys = $this->context->parameters['api']['keys'];
        }
        if($this->apikey == NULL || !in_array($this->apikey, $allowedKeys)) {
            $this->getHttpResponse()->setCode(401);
            $response = new TextResponse("API key was not included or it is invalid.");
            $this->sendResponse($response);
            $this->terminate();
        }
    }

    private function convertCoordinates($c) {
        $c = explode(',', $c);
        return [
            'latitude' => $c[0],
            'longtitude' => $c[1]
            ];
    }

    public function actionBuilding($id=NULL) {
        $result = $this->getRepository('building')->fetch(new BuildingWithFloors($id));
        $payload = [];


        /** @var $item Building */
        foreach($result as $item) {
            $payload[] = $this->getBuildingPayload($item);
        }

        $this->sendResponse(new JsonResponse($payload));
    }

    private function getBuildingPayload(Building $item) {
        $floors = [];
        foreach($item->getFloors() as $floor) {
            $floors[] = $this->getFloorPayload($floor);
        }
        $minFloor = 0;

        usort($floors, function($a, $b) {
            if($a['floor'] < $b['floor']) return -1;
            if($a['floor'] > $b['floor']) return 1;
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

    private function getFloorPayload(Floor $floor) {
        return [
            'id' => $floor->id,
            'floorName' => $floor->getName(),
            'floor' => $floor->getFloorNumber(),
            //TODO 'plan' => $floor->getPlan()->id
        ];
    }


}