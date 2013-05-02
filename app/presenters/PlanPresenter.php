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
use Maps\Model\Floor\DeactivatePlansOfFloorQuery;
use Maps\Model\Floor\Floor;
use Maps\Model\Floor\Plan;
use Maps\Model\Floor\PlanFormProcessor;
use Maps\Model\Floor\PlanRevisionsQuery;
use Maps\Model\Persistence\BaseFormProcessor;

class PlanPresenter extends SecuredPresenter {

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
            $this->addBreadcrumb('Plan:default?','Plány');
        }

        parent::beforeRender();
    }

    public function actionAdd() {

        $this['formOne']->bindEntity($this->getRepository('plan')
            ->createNew(NULL, [
                'floor'=>$this->getFloor(),
                'user'=>$this->getRepository('user')->find($this->getUser()->getId())
            ]));
    }

    public function actionMap($id) {
        $this['georeferenceForm']->bindEntity($this->getRepository('plan')->find($id));
    }

    public function actionEdit($id) {
        $this->setView('map');

        $form = $this['georeferenceForm'];
        if($form->isSubmitted()) {
            $plan = $this->getRepository('plan')->find($id);
            $form->bindEntity($this->getRepository('plan')
                ->createNew(NULL, ['floor'=>$plan->floor,
                    'user'=>$this->getRepository('user')->find($this->getUser()->getId()),
                    'sourceFile'=>$plan->sourceFile,
                    'sourceFilePage'=>$plan->sourceFilePage,
                ]));
        }
        else {
            $form->bindEntity($this->getRepository('plan')->find($id));
        }
        $this['georeferenceForm']['ok']->caption= 'Odeslat a uložit jako novu revizi';
    }

    public function handlePublish($id) {
        /** @var $plan Plan */
        $plan = $this->getRepository('plan')->find($id);
        if($plan->getPublished()) {
            $this->flashMessage("Tato revize již je publikována.", self::FLASH_ERROR);
            $this->redirect('this');
        }
        if($plan->getReferenceTopLeft() == NULL || $plan->getReferenceTopRight() == NULL ||
            $plan->getReferenceBottomRight() == NULL) {
            $this->flashMessage('Tato verze ('.$plan->getRevision().') nemá nastaveny všechny referenční body, nelze ji publikovat.', self::FLASH_ERROR);
            $this->redirect('this');
        }
        $plan->setInPublishQueue(TRUE);

        $toUnpublish = $this->getRepository('plan')->findBy(['inPublishQueue' => TRUE, 'floor' => $plan->getFloor()]);
        foreach($toUnpublish as $p) {
            if($p->id != $plan->id) {
                $p->inPublishQueue = FALSE;
            }
        }

        $this->getRepository('plan')->getEntityManager()->flush();

        $this->flashMessage('Publikace plánu byla zařazena ke zpracování do dlaždic. Vygenerování dlaždic trvá cca 5 minut.', self::FLASH_SUCCESS);
        $this->redirect('this');
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
            'toBePublished' => 'p.inPublishQueue',
                                ]);


        $grid = new DataGrid($this, $name);
        $grid->setDataSource($datasource);

        $grid->addColumn('revision', 'Revize')->addDefaultSorting('desc');
        $a = $grid->addColumn('published', 'Publikováno',0);
        $a->formatCallback[] = function ($value, $data) {
            if ($value == 1) {
                return "<span class='label label-success'><i class='icon-ok icon-white'>&nbsp;</i></span>";
            }
            if($data['toBePublished']) {
                return "<span class='label label-warning' title='Zařazeno k publikaci'><i class='icon-warning-sign icon-white'>&nbsp;</i></span>";
            }
            return $value;
        };


        $grid->addColumn('user', 'Nahrál');
        $grid->addDateColumn('added', 'Nahráno', "%d.%m.%Y %H:%M");
        $grid->addDateColumn('published_date', 'Publikováno', "%d.%m.%Y %H:%M");

        $grid->keyName = 'id';
        $grid->addActionColumn('a','Akce');
        $grid->addAction('Zobrazit', 'edit');
        $grid->addAction('Publikovat', 'publish!');
    }

    public function createComponentFormOne($name) {
        $form = new EntityForm($this, $name);

        $form->addUpload("sourceFile", "Mapový plán")
            ->setRequired()
            ->addRule($form::MIME_TYPE, 'Podporované formáty jsou PNG, GIF, JPG a PDF', ['image/*', 'application/pdf','text/pdf'])
            ->setOption('description', 'Možné formáty JPG, PNG, GIF a PDF.');
        $form->addText("sourceFilePage", "Číslo stránky")
            ->addRule($form::NUMERIC)
            ->setDefaultValue(1)
            ->setOption("description", "V případě vícestránkových dokumentů uveďte na které stránce se plán nachází.");
        $form->setEntityService(new PlanFormProcessor($this->getRepository('plan')));

        $form->addSubmit("ok","Další krok");

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

        $map->setOverlayImage('data/plans/raw/'.$plan->plan.($plan->sourceFilePage != ""?"[".$plan->sourceFilePage."]":""));
    }

    public function createComponentGeoreferenceForm($name) {
        $form = new EntityForm($this, $name);


        $form->setEntityService(new BaseFormProcessor($this->getRepository('plan')));

        $form->addText('referenceTopLeft','A')
            ->setHtmlId('topLeft');
        $form->addText('referenceBottomRight','B')
            ->setHtmlId('bottomRight');
        $form->addText('referenceTopRight','C')
            ->setHtmlId('topRight');

        $form->addSubmit('ok','Odeslat');
        $form->setRedirect('default');
    }

}