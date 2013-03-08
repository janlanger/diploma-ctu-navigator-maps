<?php
namespace Maps\Presenter;
use Maps\Components\Forms\EntityForm;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\BasicMap;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Persistence\BaseFormProcessor;
use Nette\NotImplementedException;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 10.2.13
 * Time: 17:52
 * To change this template use File | Settings | File Templates.
 */
class FloorPresenter extends SecuredPresenter{
    
    public function actionAdd($id) {
        $this->template->building = $this->getRepository('building')->find($id);
        $entity = $this->getRepository('floor')->createNew();
        $entity->setBuilding($this->template->building);
        
        $this['form']->bindEntity($entity);
    }

    public function actionEdit($id) {
        $entity = $this->getRepository('floor')->find($id);
        $this->template->building = $entity->building;

        $this['form']->bindEntity($entity);

    }

    public function actionDefault($id) {
        $this->template->floor = $floor =  $this->getRepository('floor')->find($id);
        $this->template->building = $floor->building;
        $this->template->plan = $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($floor));
        //$q2 = new GetActiveMetadataQuery($floor);
    }


    
    public function createComponentForm($name) {
        $form = new EntityForm($this, $name);
        
        $form->setEntityService(new BaseFormProcessor($this->getRepository('floor')));
        $form->addText('floorNumber', 'Číslo podlaží')
                ->setRequired()
                ->addRule(Form::NUMERIC)
                ->setOption('description','Kolikáté je toto patro nad úrovní ulice.');
        $form->addText('name', 'Popisek podlaží');
        //$form->addHidden('building');
        
        $form->addSubmit('ok','Uložit');
        $form->setRedirect('Building:detail?id='.$this->getParameter('id'));
    }

    public function createComponentMap($name) {
        $building = $this->template->building;
        $floor = $this->template->floor;
        $plan = $this->template->plan;
        $map = new BasicMap();
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($building->gpsCoordinates);

        $map->setZoomLevel(20);

        if($plan != null) {
            $map->addCustomTilesLayer($floor->name, $this->getContext()->tiles->getTilesBasePath($plan));
        }


        return $map;
    }
}
