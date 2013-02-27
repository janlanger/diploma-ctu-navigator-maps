<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.2.13
 * Time: 17:33
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Presenter;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\PolyLinesEditor;
use Maps\Model\FloorPlan\FloorPlan;

class MetadataPresenter extends SecuredPresenter{

    public function renderDefault($id) {
        $this->template->plan = $plan = $this->getRepository('plan')->find($id);
        $this->template->building = $plan->building;

        $this['pointForm']['definition']->setDefaultValue($this->encodePointData($plan));
    }

    public function createComponentPointForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $form->addHidden('building',$this->getParameter('building'));
        $form->addTextArea('definition');
        $form->addSubmit('send','Uložit');



        $form->onSuccess[] = function(Form $form) {
            $x = new \Maps\Model\FloorPlan\MetadataFormProcessor(
                $this->getRepository('plannode'),
                $this->getRepository('planpath'),
                $this->getRepository('plan')->find($this->getParameter('id'))
            );
            $x->handle($form);
            $this->redirect("this");
        };
    }

    protected function createComponentMap($name) {
        $map = new PolyLinesEditor($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);

        $map->setCenter($this->template->building->getGpsCoordinates());
        $map->setZoomLevel(20);
        $map->bindedFormField($this['pointForm']['definition']);
        $map->setSubmitButton($this['pointForm']['send']);
    }



    public function encodePointData(FloorPlan $entity) {
        $nodes = $entity->nodes->toArray(); //this is needed to lower #of queries
        $paths = $entity->paths;
        return json_encode(['paths'=>$paths->toArray()]);
    }
}