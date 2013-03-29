<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.2.13
 * Time: 17:33
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Presenter;
use DataGrid\DataGrid;
use DataGrid\DataSources\Doctrine\QueryBuilder;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\PolyLinesEditor;
use Maps\Components\GoogleMaps\ProposalEditor;
use Maps\Model\ACL\DatagridQuery;
use Maps\Model\BaseDatagridQuery;
use Maps\Model\Building\DictionaryQuery;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Floor\Floor;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\ProposalProcessor;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\ProposalsGridQuery;
use Maps\Model\Metadata\Queries\RevisionGridQuery;
use Maps\Model\Metadata\Queries\RevisionProcessor;
use Maps\Model\Metadata\Revision;
use Nette\Utils\Html;

class MetadataPresenter extends SecuredPresenter{

    /**
     * @persistent
     */
    public $floor;

    /** @var Floor */
    private $floorEntity = NULL;

    private function getFloor() {
        if($this->floorEntity == NULL) {
            $this->floorEntity = $this->getRepository('floor')->find($this->floor);
        }
        return $this->floorEntity;
    }



    protected function beforeRender()
    {
        $this->template->floor = $floor = $this->getFloor();
        $this->template->building = $building = $this->getFloor()->getBuilding();


        $this->addBreadcrumb('Building:','Budovy');
        $this->addBreadcrumb('Building:detail?id='.$building->id, $building->getName());
        $this->addBreadcrumb('Floor:default?id='.$floor->id.'&building='.$building->id, $floor->name);
        if($this->getView() != "default") {
            $this->addBreadcrumb('Metadata:default','Metadata');
        }

        parent::beforeRender(); // TODO: Change the autogenerated stub
    }

    public function renderProposal() {
        $data = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($this->getFloor()));
        if($data !=  NULL) {
            $this['pointForm']['definition']->setDefaultValue($this->encodePointData($data));
        }
        //$this['pointForm']['definition']->setDefaultValue('{"nodes":[{"id":37,"position":"50.105231054700845,14.38982605934143","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":38,"position":"50.10490591423023,14.389384165406227","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":39,"position":"50.10500096213579,14.38920110464096","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":40,"position":"50.1053269621165,14.389647021889687","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":41,"position":"50.10503880921312,14.39018614590168","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":42,"position":"50.10492784837905,14.3900366127491","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":43,"position":"50.10512452097454,14.389685065715298","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":44,"position":"50.105221172388454,14.389502213867104","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":45,"position":"50.105007780227815,14.389893741892934","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":46,"position":"50.10503149784821,14.389901161193848","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":47,"position":"50.10504052953408,14.389883056282997","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":1,"toBuilding":null},{"id":48,"position":"50.10509676314708,14.389734762863895","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":49,"position":"50.10510031064992,14.38976302742958","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":3,"toBuilding":null},{"id":50,"position":"50.10503743303543,14.389840736690303","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":51,"position":"50.10509902041081,14.38992127776146","name":null,"type":"study","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":52,"position":"50.105146528835945,14.389985965943993","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":53,"position":"50.10505039918868,14.389857680346381","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":54,"position":"50.105062463621216,14.389835447072983","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":1,"toBuilding":null},{"id":55,"position":"50.1051852285423,14.389768767283272","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":56,"position":"50.10520051911562,14.389735534787178","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":57,"position":"50.10519793864273,14.389704689383507","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":3,"toBuilding":null},{"id":58,"position":"50.10521815234326,14.389735534787178","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":1,"toBuilding":null},{"id":59,"position":"50.1051639624035,14.389808624982834","name":null,"type":"elevator","room":null,"fromFloor":-2,"toFloor":7,"toBuilding":null},{"id":60,"position":"50.10518202572356,14.389522299170494","name":null,"type":"restroom-men","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":61,"position":"50.10519972239031,14.38954310901886","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":63,"position":"50.10516954610258,14.38960013316796","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":64,"position":"50.10487752884631,14.389349296689034","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":65,"position":"50.10485430442877,14.389349296689034","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":3,"toBuilding":null},{"id":66,"position":"50.10486849712973,14.38936673104763","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":1,"toBuilding":null},{"id":67,"position":"50.10490204349707,14.390089586377144","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":68,"position":"50.10488871096926,14.39006544649601","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":1,"toBuilding":null},{"id":69,"position":"50.10487838900971,14.390086904168129","name":null,"type":"stairs","room":null,"fromFloor":null,"toFloor":3,"toBuilding":null},{"id":70,"position":"50.10531437104222,14.38962975227355","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":71,"position":"50.10533040273771,14.38960276544094","name":null,"type":"auditorium","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":72,"position":"50.10531578009588,14.389742240309715","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":73,"position":"50.10529463445319,14.38970856130436","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":74,"position":"50.10527621292496,14.389806613326073","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":75,"position":"50.105255582632765,14.389782550631253","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":76,"position":"50.10525255862239,14.389850199222565","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":77,"position":"50.1051639624035,14.390015825629234","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":78,"position":"50.10510292609252,14.39006678132182","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":79,"position":"50.10512009431213,14.390089586377144","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":80,"position":"50.105061757817815,14.390142815471336","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":81,"position":"50.105083967618484,14.390167370438576","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":82,"position":"50.10502418648224,14.390217661857605","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":83,"position":"50.10505429209972,14.390203580260277","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":84,"position":"50.10501172311947,14.39015129297718","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":85,"position":"50.104997951571534,14.390178099274635","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":86,"position":"50.104983693158,14.39011648940118","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":87,"position":"50.1049725768082,14.390136525034904","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":88,"position":"50.10495878941393,14.390080879474226","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":89,"position":"50.10494591178816,14.39009964466095","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":90,"position":"50.10490505406734,14.390023872256279","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":91,"position":"50.10497735186148,14.389948202377582","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":92,"position":"50.104956233733155,14.389917254447937","name":"Studijn\u00ed odd\u011blen\u00ed","type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":93,"position":"50.105022036080236,14.389809295535088","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":94,"position":"50.104995913506365,14.389508063848893","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":95,"position":"50.1049678459186,14.389553815126419","name":"D\u011bkan\u00e1t","type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":96,"position":"50.10488441015305,14.389409646391869","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":97,"position":"50.104916466472986,14.38936373270542","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":98,"position":"50.104898602845076,14.389341250061989","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":101,"position":"50.104961844518876,14.389276168626907","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":102,"position":"50.10494462154485,14.389258101582527","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":103,"position":"50.10498649776427,14.389228860770913","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":104,"position":"50.10496827599952,14.389207810163498","name":null,"type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":105,"position":"50.10501013830271,14.389213635471151","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":106,"position":"50.10502332632145,14.389179646968842","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":107,"position":"50.10505552940712,14.389275680367746","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":108,"position":"50.10506719450142,14.389243349432945","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":109,"position":"50.10509928035287,14.389335470785454","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":110,"position":"50.10511493335757,14.389307722449303","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":111,"position":"50.105139447886806,14.389350637793541","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":112,"position":"50.10512433640059,14.38937081382096","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":114,"position":"50.105068914821445,14.389558508992195","name":"Zaseda\u010dka","type":"office","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":115,"position":"50.10505824046954,14.389593955016267","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":116,"position":"50.10523621564288,14.38948541879654","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":117,"position":"50.105269734282864,14.389568567653328","name":null,"type":"intersection","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":118,"position":"50.105285674642715,14.389547780156136","name":null,"type":"lecture","room":null,"fromFloor":null,"toFloor":null,"toBuilding":null},{"id":119,"position":"50.105152780344795,14.389576613903046","name":"","type":"restroom-women","room":"","fromFloor":null,"toFloor":null,"toBuilding":null}],"paths":[{"startNode":0,"endNode":18},{"startNode":1,"endNode":59},{"startNode":2,"endNode":65},{"startNode":3,"endNode":35},{"startNode":0,"endNode":15},{"startNode":4,"endNode":46},{"startNode":5,"endNode":53},{"startNode":6,"endNode":74},{"startNode":6,"endNode":25},{"startNode":7,"endNode":76},{"startNode":8,"endNode":13},{"startNode":8,"endNode":9},{"startNode":9,"endNode":10},{"startNode":11,"endNode":6},{"startNode":11,"endNode":12},{"startNode":13,"endNode":11},{"startNode":13,"endNode":16},{"startNode":14,"endNode":15},{"startNode":15,"endNode":40},{"startNode":16,"endNode":14},{"startNode":16,"endNode":17},{"startNode":18,"endNode":6},{"startNode":18,"endNode":19},{"startNode":19,"endNode":20},{"startNode":21,"endNode":19},{"startNode":22,"endNode":18},{"startNode":23,"endNode":24},{"startNode":24,"endNode":7},{"startNode":25,"endNode":24},{"startNode":1,"endNode":26},{"startNode":26,"endNode":27},{"startNode":28,"endNode":26},{"startNode":5,"endNode":29},{"startNode":29,"endNode":30},{"startNode":31,"endNode":29},{"startNode":32,"endNode":3},{"startNode":32,"endNode":33},{"startNode":34,"endNode":35},{"startNode":35,"endNode":37},{"startNode":36,"endNode":37},{"startNode":37,"endNode":0},{"startNode":0,"endNode":38},{"startNode":15,"endNode":39},{"startNode":40,"endNode":42},{"startNode":40,"endNode":41},{"startNode":42,"endNode":4},{"startNode":42,"endNode":43},{"startNode":4,"endNode":44},{"startNode":4,"endNode":45},{"startNode":46,"endNode":48},{"startNode":46,"endNode":47},{"startNode":48,"endNode":50},{"startNode":48,"endNode":49},{"startNode":50,"endNode":5},{"startNode":50,"endNode":51},{"startNode":5,"endNode":52},{"startNode":53,"endNode":8},{"startNode":53,"endNode":54},{"startNode":13,"endNode":55},{"startNode":56,"endNode":1},{"startNode":56,"endNode":57},{"startNode":1,"endNode":58},{"startNode":59,"endNode":60},{"startNode":61,"endNode":63},{"startNode":61,"endNode":62},{"startNode":63,"endNode":2},{"startNode":63,"endNode":64},{"startNode":65,"endNode":67},{"startNode":65,"endNode":66},{"startNode":67,"endNode":69},{"startNode":67,"endNode":68},{"startNode":69,"endNode":72},{"startNode":69,"endNode":70},{"startNode":71,"endNode":72},{"startNode":73,"endNode":74},{"startNode":74,"endNode":56},{"startNode":7,"endNode":75},{"startNode":76,"endNode":32},{"startNode":76,"endNode":77},{"startNode":7,"endNode":72},{"startNode":61,"endNode":59},{"startNode":78,"endNode":25}]}');
    }

    public function createComponentPointForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $form->addHidden('building',$this->getParameter('building'));
        $form->addTextArea('definition');
        $form->addText('comment', 'Komentář', NULL, 50);
        $form->addSubmit('send','Uložit');



        $form->onSuccess[] = function(Form $form) {
            $x = new ProposalProcessor(
                $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($this->getFloor())),
                $this->getRepository('user')->find($this->getUser()->getId()),
                $this->getRepository("meta_node_properties"),
                $this->getRepository("meta_path_properties"),
                $this->getRepository('meta_changeset'),
                $this->getRepository('meta_node_change'),
                $this->getRepository('meta_path_change')
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

        $map->setRoomPrefix($this->template->building->roomPrefix);

        $map->bindFormField($this['pointForm']['definition']);
        $map->setSubmitButton($this['pointForm']['send']);
        $map->setNodeTypes([
            'intersection' => ['anchor'=>[4,4], 'legend'=>'Křižovatka'],
            'entrance' => ['anchor'=>[8,8], 'legend'=>'Vchod'],
            'stairs' => ['anchor'=>[8,8], 'legend' => 'Schodiště'],
            'elevator' => ['anchor'=>[8,8], 'legend'=>'Výtah'],
            'passage' => ['anchor'=>[8,8], 'legend'=>'Průchod'],
            'lecture' => ['anchor'=>[8,8], 'legend'=>'Učebna'],
            'auditorium' => ['anchor'=>[8,8], 'legend'=>'Posluchárna'],
            'office'=> ['anchor'=>[8,8], 'legend'=>'Kancelář'],
            'study'=> ['anchor'=>[8,8], 'legend'=>'Studovna'],
            'cafeteria'=> ['anchor'=>[8,8], 'legend'=>'Kantýna'],
            'restroom-men'=> ['anchor'=>[8,8], 'legend'=>'WC muži'],
            'restroom-women'=> ['anchor'=>[8,8], 'legend'=>'WC ženy'],
            'cloakroom'=> ['anchor'=>[8,8], 'legend'=>'Šatna'],
            'restriction'=> ['anchor'=>[8,8], 'legend'=>'Zákaz vstupu'],
            'default' => ['anchor'=>[8,8], 'legend'=>'Ostatní'],
        ]);
        $map->setNodeIconBase('images/markers/types');



        $map->setPathOptions([
            'strokeColor' =>'#ff0000',
            'strokeOpacity' =>0.5,
            'strokeWeight' => 1.5
        ]);

        $map->setBuildingsDictionary($this->getRepository('building')->fetchPairs(new DictionaryQuery()));

        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($this->getFloor()));
        if($plan != NULL) {
            $map->addCustomTilesLayer($this->getFloor()->name, $this->getContext()->tiles->getTilesBasePath($plan));
        }
    }



    public function encodePointData(Revision $entity) {
        $nodes = $entity->nodes->toArray();
        $paths = $entity->paths->toArray();

        $findNodeId = function($dbId) use ($nodes) {
            foreach($nodes as $index => $node) {
                if($node->properties->id == $dbId->id) {
                    return $index;
                }
            }
        };

        $pathArray = [];

        foreach($paths as $path) {
            $pathArray[] = [
                'startNode' => $findNodeId($path->properties->startNode),
                'endNode' => $findNodeId($path->properties->endNode),
                ];
        }

        return json_encode(['nodes' => $nodes, 'paths'=>$pathArray]);
    }

    public function createComponentProposalEditor($name) {
        $map = new ProposalEditor();

        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($this->getFloor()->getBuilding()->gpsCoordinates);
        $map->setZoomLevel(20);
        $map->setRoomPrefix($this->getFloor()->getBuilding()->roomPrefix);


        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($this->getFloor()));
        if ($plan != NULL) {
            $map->addCustomTilesLayer($this->getFloor()->name, $this->getContext()->tiles->getTilesBasePath($plan));
        }

        $map->setProposalRepository($this->getRepository('meta_changeset'));

        $map->setNodeTypes([
            'intersection' => ['anchor' => [4, 4], 'legend' => 'Křižovatka'],
            'entrance' => ['anchor' => [8, 8], 'legend' => 'Vchod'],
            'stairs' => ['anchor' => [8, 8], 'legend' => 'Schodiště'],
            'elevator' => ['anchor' => [8, 8], 'legend' => 'Výtah'],
            'passage' => ['anchor' => [8, 8], 'legend' => 'Průchod'],
            'lecture' => ['anchor' => [8, 8], 'legend' => 'Učebna'],
            'auditorium' => ['anchor' => [8, 8], 'legend' => 'Posluchárna'],
            'office' => ['anchor' => [8, 8], 'legend' => 'Kancelář'],
            'study' => ['anchor' => [8, 8], 'legend' => 'Studovna'],
            'cafeteria' => ['anchor' => [8, 8], 'legend' => 'Kantýna'],
            'restroom-men' => ['anchor' => [8, 8], 'legend' => 'WC muži'],
            'restroom-women' => ['anchor' => [8, 8], 'legend' => 'WC ženy'],
            'cloakroom' => ['anchor' => [8, 8], 'legend' => 'Šatna'],
            'restriction' => ['anchor' => [8, 8], 'legend' => 'Zákaz vstupu'],
            'default' => ['anchor' => [8, 8], 'legend' => 'Ostatní'],
        ]);
        $map->setNodeIconBase('images/markers/types');

        $map->setActiveRevision($this->getRepository('meta_revision')->fetchOne(new ActiveRevision($this->getFloor())));

        $map->setRevisionDictionary($this->getRepository("meta_revision")->fetchPairs(new BaseDatagridQuery(), "id","revision"));

        $map->setPathOptions([
            'strokeColor' => '#ff0000',
            'strokeOpacity' => 0.8,
            'strokeWeight' => 2
        ]);

        $map->setSubmitHandler(function (Form $form) {
            $p = new RevisionProcessor(
                $this->getRepository('user')->find($this->getUser()->getId()),
                $this->getRepository('meta_revision'),
                $this->getRepository("meta_node_properties"),
                $this->getRepository("meta_path_properties"),
                $this->getRepository('meta_changeset'),
                $this->getRepository('meta_node_change'),
                $this->getRepository('meta_path_change'),
                $this->getRepository('meta_node'),
                $this->getRepository('meta_path')
            );
            if($p->handle($form)) {
                $this->flashMessage("Data byla úspěšně uložena. Nezapomeňte novou revizi publikovat.", self::FLASH_SUCCESS);
                $this->redirect("default");
            }
        });

        return $map;
    }

    public function createComponentProposalGrid($name) {
        $grid = new DataGrid($this, $name);
        $q = new ProposalsGridQuery($this->getFloor());
        $datasource = new QueryBuilder($q->getQueryBuilder($this->getRepository('meta_changeset')));

        $datasource->setMapping([
            'date' => 'c.submitted_date',
            'state' => 'c.state',
            'user' => 'author',
            'comment'=>'c.comment',
            'processed_by' => 'name',
            "processed_date" => "c.processed_date",
            'in_revision' => "revision",
            'admin_comment' => 'c.admin_comment',
        ]);


        $grid->setDataSource($datasource);

        $states = [Changeset::STATE_NEW => 'Nový', Changeset::STATE_APPROVED => 'Přijatý', Changeset::STATE_REJECTED => 'Odmítnutý', Changeset::STATE_WITHDRAWN => 'Zrušený'];

        $grid->addDateColumn("date",'Odesláno dne', "%d.%m.%Y %H:%M");

        $grid->addColumn("state","Stav návrhu")
            ->addSelectboxFilter($states, TRUE, FALSE);
        //$grid['state']->addDefaultFiltering(Changeset::STATE_NEW);
        $grid['state']->formatCallback[] = function($value, $data) use ($states) {

            $el = Html::el('span')->setText($states[$value]);
            $el->class[] = 'label';
            switch ($value) {
                case Changeset::STATE_NEW:
                    $el->class[] = "label-info";
                    break;
                case Changeset::STATE_APPROVED:
                    $el->class[] = 'label-success';
                    break;
                case Changeset::STATE_REJECTED:
                    $el->class[] = 'label-important';
                    break;
            }
            return $el;
        };
        $grid->addColumn("user", "Navrhl");
        $grid->addColumn("processed_by", "Zpracoval");
        $grid->addColumn("in_revision", "V revizi");

        $grid['processed_by']->formatCallback[] = function($value, $data) {
            if($value != ""){
                return $value." (".$data["processed_date"]->format("d.m. H:i").")";
            }
        };

        $grid->addColumn("comment", 'Komentáře', 0);
        $grid['comment']->formatCallback[] = function($value, $data) {
            $r = "";
            if($value != "") {
                $r .= (string)Html::el("span class=badge")->setText("Uživatel")->setTitle($value);
            }
            if ($data['admin_comment'] != "") {
                $r .= ((string) Html::el("span class='badge badge-info'")->setText("Admin")->setTitle($data['admin_comment']));
            }

            return $r;

        };


        $grid['date']->addDefaultSorting('asc');



    }

    public function createComponentRevisionGrid($name) {
        $q = new RevisionGridQuery($this->getFloor());
        $ds = new QueryBuilder($q->getQueryBuilder($this->getRepository("meta_revision")));
        $ds->setMapping([
            'id' => 'r.id',
            'revision' => 'r.revision',
            'published' => 'r.published',
            'user' => 'u.name',
            'published_date' => 'r.published_date',
        ]);


        $grid = new DataGrid($this, $name);
        $grid->setDataSource($ds);

        $grid->addColumn('revision', 'Revize')->addDefaultSorting('desc');
        $grid->addColumn('published', 'Aktivní');
        $grid->addDateColumn('published_date', 'Publikováno', "%d.%m.%Y %H:%M");
        $grid->addColumn('user', 'Vytvořil');

        $grid->keyName = 'id';
        $grid->addActionColumn('a', 'Akce');
        $grid->addAction('Zobrazit', 'edit');
        $grid->addAction('Publikovat', 'publish!');

        $grid['published']->formatCallback[] = function($value, $data) {
            if($value == 1) {
                return "<span class='label label-success'><i class='icon-ok icon-white'>&nbsp;</i></span>";
            }
            return $value;
        };

    }
}