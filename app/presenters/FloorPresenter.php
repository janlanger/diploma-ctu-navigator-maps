<?php
namespace Maps\Presenter;
use App\Model\Proxies\__CG__\Maps\Model\Building\Building;
use Maps\Components\Forms\EntityForm;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\BasicMap;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Floor\Node;
use Maps\Model\Floor\Path;
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

    /** @persistent */
    public $building;

    private $buildingEntity = null;

    /**
     * @return Building
     */
    private function getBuilding() {
        if($this->buildingEntity == null) {
            $this->buildingEntity = $this->getRepository('building')->find($this->building);
        }
        return $this->buildingEntity;
    }

    protected function beforeRender()
    {
        $this->template->building = $this->getBuilding();
        $this->addBreadcrumb('Building:','Budovy');
        $this->addBreadcrumb('Building:detail?id='.$this->getBuilding()->id, $this->getBuilding()->getName());

        parent::beforeRender();
    }


    public function actionAdd() {
        $entity = $this->getRepository('floor')->createNew();
        $entity->setBuilding($this->getBuilding());
        
        $this['form']->bindEntity($entity);
    }

    public function actionEdit($id) {
        $entity = $this->getRepository('floor')->find($id);

        $this['form']->bindEntity($entity);

    }

    public function actionDefault($id) {
        $this->template->floor = $floor =  $this->getRepository('floor')->find($id);
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
        $building = $this->getBuilding();
        $floor = $this->template->floor;
        $plan = $this->template->plan;



        $map = new BasicMap();
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($building->gpsCoordinates);

        $map->setZoomLevel(20);

        if($plan != null) {
            $map->addCustomTilesLayer($floor->name, $this->getContext()->tiles->getTilesBasePath($plan));
        }

        $map->setPointsInfo([
            'intersection' => ['url'=>'/images/markers/dots/mini.png','anchor'=>[4,4], 'legend'=>'Křižovatka'],
            'entrance' => ['url'=>'/images/markers/dots/light_green.png','anchor'=>[4,4], 'legend'=>'Vchod'],
            'stairs' => ['url'=>'/images/markers/dots/light_purple.png','anchor'=>[4,4], 'legend' => 'Schodiště'],
            'elevator' => ['url'=>'/images/markers/dots/light_yellow.png','anchor'=>[4,4], 'legend'=>'Výtah'],
            'passage' => ['url'=>'/images/markers/dots/light_blue.png','anchor'=>[4,4], 'legend'=>'Průchod'],
            'lecture' => ['url'=>'/images/markers/dots/red.png','anchor'=>[4,4], 'legend'=>'Učebna'],
            'auditorium' => ['url'=>'/images/markers/dots/red.png','anchor'=>[4,4], 'legend'=>'Posluchárna'],
            'office'=> ['url'=>'/images/markers/dots/green.png','anchor'=>[4,4], 'legend'=>'Kancelář'],
            'study'=> ['url'=>'/images/markers/dots/yellow.png','anchor'=>[4,4], 'legend'=>'Studovna'],
            'cafeteria'=> ['url'=>'/images/markers/dots/dark_blue.png','anchor'=>[4,4], 'legend'=>'Kantýna'],
            'restroom-men'=> ['url'=>'/images/markers/dots/purple.png','anchor'=>[4,4], 'legend'=>'WC muži'],
            'restroom-women'=> ['url'=>'/images/markers/dots/purple.png','anchor'=>[4,4], 'legend'=>'WC ženy'],
            'cloakroom'=> ['url'=>'/images/markers/dots/pink.png','anchor'=>[4,4], 'legend'=>'Šatna'],
            'restriction'=> ['url'=>'/images/markers/dots/dark_red.png','anchor'=>[4,4], 'legend'=>'Zákaz vstupu'],
            'default' => ['url'=>'/images/markers/dots/light_red.png','anchor'=>[4,4], 'legend'=>'Ostatní'],
        ]);
        $nodes = $floor->nodes;

        $types = [
            'entrance' => 'Vchod',
            'stairs' => 'Schodiště',
            'elevator' => 'Výtah',
            'passage' => 'Průchod',
            'lecture' => 'Učebna',
            'office' => 'Kancelář',
            'study' => 'Studovna',
            'auditorium' => 'Posluchárna',
            'cafeteria' => 'Kantýna',
            'restroom-men' => 'WC muži',
            'restroom-women' => 'WC ženy',
            'cloakroom' => 'Šatna',
            'other' => '',
            'restriction' => 'Zákaz vstupu',
        ];

        /** @var $node Node */
        foreach($nodes as $node) {
            $title = [];
            if(isset($types[$node->type])) {
                if($types[$node->type]) {
                    $title[] = $types[$node->type];
                }

                if($node->getName() != "") {
                    $title[] = $node->getName();
                }

                if($node->getRoom() != "") {
                    $title[] = $node->getRoom();
                }

            }
            $map->addPoint($node->getPosition(), [
                'draggable'=>false,
                'title' => (!empty($title)?implode(" - ", $title):null),
                'type' => $node->type,
            ]);
        }

        $paths = $floor->paths;

        $map->setPathOptions([
            'strokeColor' =>'#aa0000',
            'strokeOpacity' =>0.5,
            'strokeWeight' => 1.5
        ]);

        /** @var $path Path */
        foreach($paths as $path) {
            $map->addPath($path->getStartNode()->position, $path->getEndNode()->position);
        }

        $map->showLegend(true);




        return $map;
    }
}
