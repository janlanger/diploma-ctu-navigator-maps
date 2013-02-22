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
        
        $this['form']->bindEntity($this->getRepository('plan')->createNew());
    }

    protected function createComponentMap($name) {
        $map = new \Maps\Components\GoogleMaps\PolyLinesEditor($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $id = $this->getParameter('building');

        /** @var $entity \Maps\Model\Building\Building */
        $entity = $this->getRepository('building')->find($id);
        $map->setCenter($entity->getGpsCoordinates());
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
        $form->addHidden('building',$this->getParameter('id'));
        
        $form->addSubmit('ok','Uložit');
        $form->setRedirect('Building:detail?id='.$this->getParameter('id'));
    }
}
