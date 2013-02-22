<?php
namespace Maps\Presenter;
use Maps\Components\Forms\EntityForm;
use Maps\Components\Forms\Form;
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 10.2.13
 * Time: 17:52
 * To change this template use File | Settings | File Templates.
 */
class PlanPresenter extends SecuredPresenter{
    
    public function actionAdd($id) {
        $this->template->building = $this->getRepository('building')->find($id);
        $entity = $this->getRepository('plan')->createNew();
        $entity->setBuilding($this->template->building);
        
        $this['form']->bindEntity($entity);
    }
    
    public function actionMetadata($id) {
        $this->template->plan = $plan = $this->getRepository('plan')->find($id);
        $this->template->building = $plan->building;
        
        $this['pointForm']['definition']->setDefaultValue($this->encodePointData($plan));
    }

    protected function createComponentMap($name) {
        $map = new \Maps\Components\GoogleMaps\PolyLinesEditor($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);

        $map->setCenter($this->template->building->getGpsCoordinates());
        $map->setZoomLevel(20);
        $map->bindedFormField($this['pointForm']['definition']);
        $map->setSubmitButton($this['pointForm']['send']);
    }

    public function createComponentPointForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $form->addHidden('building',$this->getParameter('building'));
        $form->addTextArea('definition');
        $form->addSubmit('send','Uložit');
        
        
        
        $form->onSuccess[] = function(\Maps\Components\Forms\Form $form) {
            $values = $form->getValues();
            dump(json_decode($values['definition']));
            exit;
        };
    }
    
    public function createComponentForm($name) {
        $form = new EntityForm($this, $name);
        
        $form->setEntityService(new \Maps\Model\FloorPlan\PlanFormProcessor($this->getRepository('plan')));
        $form->addText('floorNumber', 'Číslo podlaží')
                ->setRequired()
                ->addRule(Form::NUMERIC)
                ->setOption('description','Kolikáté je toto patro nad úrovní ulice.');
        $form->addText('name', 'Popisek podlaží');
        $form->addUpload('floorPlan', 'Mapový podklad');
        $form->addHidden('building');
        
        $form->addSubmit('ok','Uložit');
        $form->setRedirect('Building:detail?id='.$this->getParameter('id'));
    }
    
    public function encodePointData(\Maps\Model\FloorPlan\FloorPlan $entity) {
        $nodes = $entity->nodes;
        $paths = $entity->paths;
        return json_encode(['nodes'=>$nodes,'paths'=>$paths]);
    }
}
