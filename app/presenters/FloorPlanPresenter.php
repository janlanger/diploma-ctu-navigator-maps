<?php
namespace Maps\Presenter;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 10.2.13
 * Time: 17:52
 * To change this template use File | Settings | File Templates.
 */
class FloorPlanPresenter extends SecuredPresenter{

    protected function createComponentMap($name) {
        $map = new \Maps\Components\GoogleMaps\PolyLinesEditor($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $id = $this->getParameter('building');

        /** @var $entity \Maps\Model\Building\Building */
        $entity = $this->getRepository('building')->find($id);
        $map->setCenter($entity->getGpsCoordinates());
        $map->setZoomLevel(20);
    }
}
