<?php
namespace Maps\Presenter;
use App\Model\Proxies\__CG__\Maps\Model\Building\Building;
use Maps\Components\Forms\EntityForm;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\BasicMap;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Floor\Node;
use Maps\Model\Floor\Path;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\CountUnprocessedProposals;
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

    private $buildingEntity = NULL;

    /**
     * @return Building
     */
    private function getBuilding() {
        if($this->buildingEntity == NULL) {
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
        $this->template->metadata = $metadata = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($floor));

        $proposal = $this->getRepository('meta_changeset')->fetchOne(new CountUnprocessedProposals($metadata));
        if(!empty($proposal)) {
            $this->template->unprocessedProposals = array_shift($proposal);
        }
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
        $metadata = $this->template->metadata;



        $map = new BasicMap();
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($building->gpsCoordinates);

        $map->setZoomLevel(20);

        if($plan != NULL) {
            $map->addCustomTilesLayer($floor->name, $this->getContext()->tiles->getTilesBasePath($plan));
        }

        $map->setNodeTypes([
            'intersection' => ['url'=>'mini.png','anchor'=>[4,4], 'legend'=>'Křižovatka'],
            'entrance' => ['url'=>'light_green.png','anchor'=>[4,4], 'legend'=>'Vchod'],
            'stairs' => ['url'=>'light_purple.png','anchor'=>[4,4], 'legend' => 'Schodiště'],
            'elevator' => ['url'=>'light_yellow.png','anchor'=>[4,4], 'legend'=>'Výtah'],
            'passage' => ['url'=>'light_blue.png','anchor'=>[4,4], 'legend'=>'Průchod'],
            'lecture' => ['url'=>'red.png','anchor'=>[4,4], 'legend'=>'Učebna'],
            'auditorium' => ['url'=>'red.png','anchor'=>[4,4], 'legend'=>'Posluchárna'],
            'office'=> ['url'=>'green.png','anchor'=>[4,4], 'legend'=>'Kancelář'],
            'study'=> ['url'=>'yellow.png','anchor'=>[4,4], 'legend'=>'Studovna'],
            'cafeteria'=> ['url'=>'dark_blue.png','anchor'=>[4,4], 'legend'=>'Kantýna'],
            'restroom-men'=> ['url'=>'purple.png','anchor'=>[4,4], 'legend'=>'WC muži'],
            'restroom-women'=> ['url'=>'purple.png','anchor'=>[4,4], 'legend'=>'WC ženy'],
            'cloakroom'=> ['url'=>'pink.png','anchor'=>[4,4], 'legend'=>'Šatna'],
            'restriction'=> ['url'=>'dark_red.png','anchor'=>[4,4], 'legend'=>'Zákaz vstupu'],
            'default' => ['url'=>'light_red.png','anchor'=>[4,4], 'legend'=>'Ostatní'],
        ]);

        $map->setNodeIconBase("images/markers/dots");
        $nodes = $metadata->nodes;

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
            if(isset($types[$node->properties->type])) {
                if($types[$node->properties->type]) {
                    $title[] = $types[$node->properties->type];
                }

                if($node->properties->getName() != "") {
                    $title[] = $node->properties->getName();
                }

                if($node->properties->getRoom() != "") {
                    $title[] = $node->properties->getRoom();
                }

            }
            $map->addPoint($node->properties->getPosition(), [
                'draggable'=>FALSE,
                'title' => (!empty($title)?implode(" - ", $title):NULL),
                'type' => $node->properties->type,
            ]);
        }

        $paths = $metadata->paths;

        $map->setPathOptions([
            'strokeColor' =>'#aa0000',
            'strokeOpacity' =>0.5,
            'strokeWeight' => 1.5
        ]);

        /** @var $path Path */
        foreach($paths as $path) {
            $map->addPath($path->properties->getStartNode()->position, $path->properties->getEndNode()->position);
        }

        $map->showLegend(TRUE);




        return $map;
    }
}
