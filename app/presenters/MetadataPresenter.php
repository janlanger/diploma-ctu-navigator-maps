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
use DependentSelectBox\JsonDependentSelectBox;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\BasicMap;
use Maps\Components\GoogleMaps\ModalMap;
use Maps\Components\GoogleMaps\PolyLinesEditor;
use Maps\Components\GoogleMaps\ProposalEditor;
use Maps\Model\BaseDatagridQuery;
use Maps\Model\Building\DictionaryQuery;
use Maps\Model\Dao;
use Maps\Model\Floor\ActivePlanQuery;
use Maps\Model\Floor\Floor;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\Node;
use Maps\Model\Metadata\NonBlockingHttp;
use Maps\Model\Metadata\ProposalProcessor;
use Maps\Model\Metadata\Queries\ActiveRevision;
use Maps\Model\Metadata\Queries\FloorExchangePaths;
use Maps\Model\Metadata\Queries\OtherAffectedFloors;
use Maps\Model\Metadata\Queries\ProposalsGridQuery;
use Maps\Model\Metadata\Queries\RevisionDictionary;
use Maps\Model\Metadata\Queries\RevisionGridQuery;
use Maps\Model\Metadata\Queries\RevisionProcessor;
use Maps\Model\Metadata\Revision;
use Nette\Application\Responses\JsonResponse;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\Utils\Html;

class MetadataPresenter extends SecuredPresenter {

    /**
     * @persistent
     */
    public $floor;

    /** @var Floor */
    private $floorEntity = NULL;

    private function getFloor() {
        if ($this->floorEntity == NULL) {
            $this->floorEntity = $this->getRepository('floor')->find($this->floor);
        }
        return $this->floorEntity;
    }


    protected function beforeRender() {
        $this->template->floor = $floor = $this->getFloor();
        $this->template->building = $building = $this->getFloor()->getBuilding();


        $this->addBreadcrumb('Building:', 'Budovy');
        $this->addBreadcrumb('Building:detail?id=' . $building->id, $building->getName());
        $this->addBreadcrumb('Floor:default?id=' . $floor->id . '&building=' . $building->id, $floor->name);
        if ($this->getView() != "default") {
            $this->addBreadcrumb('Metadata:default', 'Metadata');
        }

        parent::beforeRender(); // TODO: Change the autogenerated stub
    }

    public function actionView($id) {
        $this->template->revision = $this->getRepository('meta_revision')->find($id);
    }

    public function createComponentViewMap($name) {
        $map = new BasicMap($this, $name);

        $revision = $this->template->revision;

        $map->setApikey($this->getContext()->parameters['google']['apiKey']);

        $map->setCenter($this->getFloor()->getBuilding()->gpsCoordinates);
        $map->setZoomLevel(20);

        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($this->getFloor()));
        if ($plan != NULL) {
            $map->addCustomTilesLayer(0, $this->getContext()->tiles->getTilesBasePath($plan));
        }

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
        $map->showLegend(TRUE);

        foreach($revision->nodes as $node) {
            $map->addPoint($node->properties->position, [
                'type'=>$node->properties->type,
                'draggable' => FALSE,
                'title'=>$node->properties->getReadableTitle(),
            ]);
        }

        $paths = $revision->paths;

        $map->setPathOptions([
            'strokeColor' => '#aa0000',
            'strokeOpacity' => 0.5,
            'strokeWeight' => 1.5
        ]);

        /** @var $path Path */
        foreach ($paths as $path) {
            $map->addPath($path->properties->getStartNode()->position, $path->properties->getEndNode()->position);

        }

    }

    public function renderProposal() {
        $data = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($this->getFloor()));
        if ($data != NULL) {
            $this['pointForm']['definition']->setDefaultValue($this->encodePointData($data));
        }
    }

    public function handlePublish($id) {
        try {
            $this->getRepository('meta_revision')->transactional(function (Dao $repository) use ($id) {
                /** @var $oldOne Revision */
                $oldOne = $repository->fetchOne(new ActiveRevision($this->getFloor()));
                $oldOne->setPublished(FALSE);

                $newOne = $repository->find($id);
                $newOne->setPublished(TRUE);
                $newOne->setPublishedDate(new \DateTime());

                $repository->save();

                //inform core

                $data = $this->getRepository('meta_floor_connection')->fetch(new OtherAffectedFloors([$oldOne, $newOne]));
                $floors = [$newOne->getFloor()->id];
                foreach($data as $x) {
                    $floors[] = $x['id'];
                }
                $floors = array_unique($floors);

                $url = new Url($this->getContext()->parameters['coreUpdatePing']['url']);
                $url->appendQuery(['floors' => $floors]);

                $request = new NonBlockingHttp($url->getAbsoluteUrl());
                $request->setNoHttpsCheck(TRUE);
                $request->addHeader("Authorization", $this->getContext()->parameters['coreUpdatePing']['apikey']);

                $request->execute();
            });

            $this->flashMessage("Revize byla publikována.", self::FLASH_SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage("Nepodařilo se publikovat revizi.", self::FLASH_ERROR);
        }

        $this->redirect("default");
    }

    public function createComponentPointForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $form->addHidden('building', $this->getParameter('building'));
        $form->addTextArea('definition');
        $form->addText('comment', 'Komentář', NULL, 50);
        $form->addSubmit('send', 'Uložit');


        $form->onSuccess[] = function (Form $form) {
            $user = $this->getRepository('user')->find($this->getUser()->getId());
            $revision = $this->getOrCreateActiveRevision($user);
            $x = new ProposalProcessor(
                $revision, $user,
                $this->getRepository("meta_node_properties"),
                $this->getRepository("meta_path_properties"),
                $this->getRepository('meta_changeset'),
                $this->getRepository('meta_node_change'),
                $this->getRepository('meta_path_change')
            );
            $x->handle($form);
            if($this->getUser()->isInRole('admin'))
                $this->redirect("Floor:default?id=".$this->getFloor()->id.'&building='.$this->getFloor()->getBuilding()->id);
            else
                $this->redirect('Dashboard:default');
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


        $map->setPathOptions([
            'strokeColor' => '#ff0000',
            'strokeOpacity' => 0.5,
            'strokeWeight' => 1.5
        ]);

        $map->setBuildingsDictionary($this->getRepository('building')->fetchPairs(new DictionaryQuery()));

        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($this->getFloor()));
        if ($plan != NULL) {
            $map->addCustomTilesLayer(0, $this->getContext()->tiles->getTilesBasePath($plan));
        }
    }


    public function encodePointData(Revision $entity) {
        $nodes = $entity->nodes->toArray();
        $paths = $entity->paths->toArray();

        $findNodeId = function ($dbId) use ($nodes) {
            foreach ($nodes as $index => $node) {
                if ($node->properties->id == $dbId->id) {
                    return $index;
                }
            }
        };

        $pathArray = [];

        foreach ($paths as $path) {
                $pathArray[] = [
                    'startNode' => $findNodeId($path->properties->startNode),
                    'endNode' => $findNodeId($path->properties->endNode),
                ];

        }

        return json_encode(['nodes' => $nodes, 'paths' => $pathArray]);
    }

    public function createComponentProposalEditor($name) {
        $map = new ProposalEditor();

        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($this->getFloor()->getBuilding()->gpsCoordinates);
        $map->setZoomLevel(20);
        $map->setRoomPrefix($this->getFloor()->getBuilding()->roomPrefix);
        $map->setBuildingsDictionary($this->getRepository('building')->fetchPairs(new DictionaryQuery()));


        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($this->getFloor()));
        if ($plan != NULL) {
            $map->addCustomTilesLayer(0, $this->getContext()->tiles->getTilesBasePath($plan));
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
        $map->setActiveRevision($this->getOrCreateActiveRevision());

        $map->setRevisionDictionary($this->getRepository("meta_revision")->fetchPairs(new RevisionDictionary($this->getFloor()), "id", "revision"));

        $map->setPathOptions([
            'strokeColor' => '#ff0000',
            'strokeOpacity' => 0.8,
            'strokeWeight' => 2
        ]);

        $map->setSubmitHandler(function (Form $form) {
            $user = $this->getRepository('user')->find($this->getUser()->getId());
            $revision = $this->getOrCreateActiveRevision($user);
            $p = new RevisionProcessor(
                $revision, $user,
                $this->getRepository('meta_revision'),
                $this->getRepository("meta_node_properties"),
                $this->getRepository("meta_path_properties"),
                $this->getRepository('meta_changeset'),
                $this->getRepository('meta_node_change'),
                $this->getRepository('meta_path_change'),
                $this->getRepository('meta_node'),
                $this->getRepository('meta_path'),
                $this->getRepository('meta_floor_connection')
            );
            if ($p->handle($form)) {
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
            'comment' => 'c.comment',
            'processed_by' => 'u2_name',
            "processed_date" => "c.processed_date",
            'in_revision' => "r2_revision",
            'admin_comment' => 'c.admin_comment',
        ]);


        $grid->setDataSource($datasource);
        $grid->itemsPerPage = 10;

        $states = [Changeset::STATE_NEW => 'Nový', Changeset::STATE_APPROVED => 'Přijatý', Changeset::STATE_REJECTED => 'Odmítnutý', Changeset::STATE_WITHDRAWN => 'Zrušený'];

        $grid->addDateColumn("date", 'Odesláno dne', "%d.%m.%Y %H:%M");

        $c = $grid->addColumn("state", "Stav návrhu");
        $c->addSelectboxFilter($states, TRUE, FALSE);
        $c->addDefaultFiltering(Changeset::STATE_NEW);
        //$grid['state']->addDefaultFiltering(Changeset::STATE_NEW);
        $grid['state']->formatCallback[] = function ($value, $data) use ($states) {

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

        $grid['processed_by']->formatCallback[] = function ($value, $data) {
            if ($value != "") {
                return $value . " (" . $data["processed_date"]->format("d.m. H:i") . ")";
            }
        };

        $grid->addColumn("comment", 'Komentáře', 0);
        $grid['comment']->formatCallback[] = function ($value, $data) {
            $r = "";
            if ($value != "") {
                $r .= (string)Html::el("span class=badge")->setText("Uživatel")->setTitle($value);
            }
            if ($data['admin_comment'] != "") {
                $r .= ((string)Html::el("span class='badge badge-info'")->setText("Admin")->setTitle($data['admin_comment']));
            }

            return $r;

        };


        $grid['date']->addDefaultSorting('desc');


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
        $grid->addAction('Zobrazit', 'view');
        $grid->addAction('Publikovat', 'publish!');
        $grid->itemsPerPage = 10;
        $grid['published']->formatCallback[] = function ($value, $data) {
            if ($value == 1) {
                return "<span class='label label-success'><i class='icon-ok icon-white'>&nbsp;</i></span>";
            }
            return $value;
        };

    }

    private function getOrCreateActiveRevision($user = NULL) {
        static $revision;
        if($revision != NULL) {
            return $revision;
        }
        if($user == NULL) {
            $user = $this->getRepository('user')->find($this->getUser()->getId());
        }
        $revision = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($this->getFloor()));

        if ($revision == NULL) {
            $revision = $this->getRepository('meta_revision')->createNew(NULL, array(
                'floor' => $this->getFloor(),
                'user' => $user,
                'published' => TRUE,
                'publishedDate' => new \DateTime(),
            ));
            $this->getRepository('meta_revision')->add($revision);
        }
        return $revision;
    }

    public function renderModalMap($coords = NULL){
        JsonDependentSelectBox::tryJsonResponse($this);
       if($this->getHttpRequest()->isAjax() && !$this['modalForm']->isSubmitted()) {
            $this->invalidateControl('modal');
        }
    }

    public function actionModalMapPoints() {
        $payload = $this['modalMap']->getPayload();
        $this->payload->data = $payload;
        $this->sendPayload();
    }

    public function createComponentModalForm($name) {
        $form = new Form($this, $name);

        $form->addSelect("building", 'Budova', $this->getRepository('building')->fetchPairs(new DictionaryQuery()))
                ->setDefaultValue($this->getFloor()->getBuilding()->id);
        $form->addDependedSelect('floor', 'Podlaží', $form['building'], callback($this, 'getFloorDictionary'), FALSE)
                ->setDefaultValue($this->getFloor()->id)
                ->setHtmlId('floors-select');
        $form->addSubmit('ok', 'Načíst podlaží')
                ->setHtmlId('floors-submit')
                ->onClick[] = function (SubmitButton $button) {
            $this->actionModalMapPoints();
        };
        return $form;
    }

    public function getFloorDictionary(Form $form) {
        static $cache = [];
        $values = $form->getValues();

        $r = [];

        if ($values['building'] > 0) {
            if(isset($cache[$values['building']])) {
                return $cache[$values['building']];
            }
            $r = $cache[$values['building']] = $this->getRepository('floor')->fetchPairs(new \Maps\Model\Floor\DictionaryQuery($values['building']), 'id', 'name');
            if ($r == NULL) {
                $r = array();
            }
        }
        return $r;
    }

    public function createComponentModalMap() {
        $map = new ModalMap();
        $floor = $this->getRepository('floor')->find($this['modalForm']['floor']->getValue());

        $map->setApiKey($this->getContext()->parameters['google']['apiKey']);
        if($this->getParameter('coords') != NULL) {
            $map->setCenter($this->getParameter('coords'));
        } else {
            $map->setCenter($floor->getBuilding()->gpsCoordinates);
        }
        $map->setZoomLevel(20);
        $plan = $this->getRepository('plan')->fetchOne(new ActivePlanQuery($floor));
        if ($plan != NULL) {
            $map->addCustomTilesLayer(0, $this->getContext()->tiles->getTilesBasePath($plan));
        }

        $metadata = $this->getRepository('meta_revision')->fetchOne(new ActiveRevision($floor));

        if ($metadata != NULL) {
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
            $nodes = $metadata->nodes;

            /** @var $node Node */
            foreach ($nodes as $node) {
                $map->addPoint($node->properties->gpsCoordinates, [
                    "draggable" => FALSE,
                    "title" => $node->getProperties()->getReadableTitle(),
                    "type" => $node->properties->type,
                    "appOptions" => json_encode($node),
                ]);
            }

            $paths = $metadata->paths;

            $map->setPathOptions([
                'strokeColor' => '#aa0000',
                'strokeOpacity' => 0.5,
                'strokeWeight' => 1.5
            ]);

            /** @var $path Path */
            foreach ($paths as $path) {
                    $map->addPath($path->properties->getStartNode()->position, $path->properties->getEndNode()->position);
            }
        }

        return $map;
    }
}