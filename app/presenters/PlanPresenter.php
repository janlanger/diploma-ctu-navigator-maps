<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 27.2.13
 * Time: 17:30
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Presenter;


use DataGrid\DataGrid;
use DataGrid\DataSources\Doctrine\QueryBuilder;
use Maps\Components\Forms\EntityForm;
use Maps\Components\Forms\Form;
use Maps\Components\GoogleMaps\OverlayPlacement;
use Maps\Model\Floor\PlanFormProcessor;
use Maps\Model\Floor\PlanRevisionsQuery;

class PlanPresenter extends SecuredPresenter {

    /**
     * @persistent
     */
    public $floor;

    public function actionDefault() {
        $floor = $this->getRepository('floor')->find($this->floor);
        $this->template->floor = $floor;
        $this->template->building = $floor->building;
    }

    public function actionAdd() {
        $floor = $this->getRepository('floor')->find($this->floor);
        $this->template->floor = $floor;
        $this->template->building = $floor->building;
        $this['formOne']->bindEntity($this->getRepository('plan')->createNew(null, ['floor'=>$floor, 'user'=>$this->getRepository('user')->find($this->getUser()->getId())]));
    }

    public function renderMap($id) {
        $floor = $this->getRepository('floor')->find($this->floor);
        $this->template->floor = $floor;
        $this->template->building = $floor->building;
    }

    public function createComponentGrid($name) {
        $q = new PlanRevisionsQuery($this->floor);
        $datasource = new QueryBuilder($q->getQueryBuilder($this->getRepository('plan')));
        $datasource->setMapping([
                                'id' => 'p.id',
                                'revision' => 'p.revision',
                                'published' => 'p.published',
                                'user' => 'name',
            'added' => 'p.added_date',
            'published_date' => 'p.published_date',
                                ]);


        $grid = new DataGrid($this, $name);
        $grid->setDataSource($datasource);

        $grid->addColumn('revision', 'Revize')->addDefaultSorting('desc');
        $grid->addColumn('published', 'Aktivní');

        $grid->addColumn('user', 'Nahrál');
        $grid->addDateColumn('added', 'Nahráno');
        $grid->addDateColumn('published_date', 'Publikováno');
    }

    public function createComponentFormOne($name) {
        $form = new EntityForm($this, $name);

        $form->addUpload("plan", "Mapový plán")
            ->setRequired()
            ->addRule($form::MIME_TYPE, 'Podporované formáty jsou PNG, GIF, JPG a PDF', ['image/*', 'application/pdf','text/pdf'])
            ->setOption('description', 'Možné formáty JPG, PNG, GIF a PDF.');
        $form->addText("pageNumber", "Číslo stránky")
            ->addRule($form::NUMERIC)
            ->setDefaultValue(1)
            ->setOption("description", "V případě PDF dokumentu uveďte na které stránce se plán nachází.");
        $form->setEntityService(new PlanFormProcessor($this->getRepository('plan')));

        $form->addSubmit("ok","Další krok");
        $form->onHandle[] = function($entity, $values) {
            $this->getSession('planManagement')->pageNumber = $values['pageNumber'];
        };
        $form->onComplete[] = function($entity) use ($form) {
            $form->setRedirect('map?id='.$entity->id);
        };

    }

    public function createComponentMap($name) {
        $map = new OverlayPlacement($this, $name);

        $plan = $this->getRepository('plan')->find($this->getParameter('id'));

        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setCenter($plan->floor->building->gpsCoordinates);
        $map->setZoomLevel(20);

        $map->setOverlayImage('data/plans/raw/'.$plan->plan);



    }

}