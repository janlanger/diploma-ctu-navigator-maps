<?php
namespace Maps\Presenter;
use Maps\Model\Building\BuildingFormProcessor;
use DataGrid\DataSources\Doctrine\QueryBuilder;
use Maps\Model\Building\Queries\BuildingDatagridQuery;
use Maps\Model\Persistence\BaseFormProcessor;
use Nette\Utils\Html;

/**
 * Class BuildingPresenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class BuildingPresenter extends SecuredPresenter {
    /** {@inheritdoc} */
    protected function beforeRender()
    {
        if($this->getView() != 'default') {
            $this->addBreadcrumb('Building:','Budovy');
        }
        parent::beforeRender();
    }

    /**
     * @param int $id building ID
     */
    public function actionDetail($id) {
        $this->template->building = $this->context->buildingRepository->find($id);
    }

    public function actionAdd() {
        $this['form']->bindEntity($this->context->buildingRepository->createNew());
        $this['googleMapGeocoder']->setCenter("50.087547,14.433289");
        $this['googleMapGeocoder']->setZoomLevel(12);

    }

    /**
     * @param int $id building ID
     */
    public function actionEdit($id) {
        $this['form']->bindEntity($this->context->buildingRepository->find($id));
    }

    /**
     * @param int $id building ID
     */
    public function handleDelete($id) {
        try {
            $entity = $this->context->buildingRepository->find($id);
            $this->context->buildingRepository->delete($entity);
            $this->flashMessage("Záznam byl úspěšně smazán", self::FLASH_SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage('Záznam nebyl smazán. ' . $e->getMessage(), self::FLASH_ERROR);
        }
        $this->redirect('default');
    }

    public function createComponentBuildingsGrid($name) {
        $grid = new \DataGrid\DataGrid($this, $name);
        $query = new BuildingDatagridQuery();
        $ds = new QueryBuilder($query->getQueryBuilder($this->getContext()->em->getRepository('Maps\Model\Building\Building')));
        $ds->setMapping([
            'id'=>'b.id',
            'name'=>'b.name',
            'address'=>'b.address',
            'proposals' => 'change_count'
        ]);

        $grid->setDataSource($ds);

        $grid->addColumn("name","Budova")->addFilter();
        $grid->addColumn("address","Adresa")->addFilter();
        $grid->addColumn("proposals", "Nové návrhy")->formatCallback[] = function($value, $data) {
            if($value > 0) {
                return Html::el("span", ['class'=>'label label-warning'])->setText($value);
            }
            return $value;
        };

        $grid['name']->addDefaultSorting('asc');
        $grid->keyName = "id";
        $grid->addActionColumn("a","Akce");
        $grid->addAction("Detail", "Building:detail");
    }

    /**
     * @param $name
     * @return \Maps\Components\GoogleMaps\BasicMap
     */
    private function googleMapBase($name) {
        $map = new \Maps\Components\GoogleMaps\BasicMap($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        $map->setZoomLevel(16);
        return $map;
    }

    protected function createComponentGoogleMap($name) {
       $map = $this->googleMapBase($name);

        /** @var $entity \Maps\Model\Building\Building */
        $entity = $this->context->buildingRepository->find($this->getParameter('id'));
        if($entity->getGpsCoordinates() != NULL) {
            $map->setCenter($entity->getGpsCoordinates());
            $map->addPoint($entity->getGpsCoordinates());
        }
    }

    protected function createComponentGoogleMapGeocoder($name) {
        $map = $this->googleMapBase($name);
        $map->enableGeodecoder($this['form']['address'],$this['form']['gpsCoordinates']);
    }

    public function createComponentForm($name) {
        $form = new \Maps\Components\Forms\EntityForm($this, $name);
        $form->setEntityService(new BaseFormProcessor($this->context->buildingRepository));

        $form->addText('name','Název')
            ->setRequired();
        $form->addText('address','Adresa')
            ->setRequired();
        $form->addText('floorCount','Počet podlaží')
            ->addRule(\Maps\Components\Forms\Form::NUMERIC)
            ->setRequired();
        $form->addText('roomPrefix','Prefix místností', NULL, 10)
            ->setRequired();
        $form->addText('gpsCoordinates', "GPS souřadnice")
            ->setRequired();
        $form->addSubmit('send','Odeslat');
        $id = $this->getParameter('id');
        if($id == NULL) {
            $form->setRedirect("default");
        } else {
            $form->setRedirect("detail?id=".$id);
        }
    }
    
    public function createComponentPlansGrid($name) {
        $grid = new \DataGrid\DataGrid($this, $name);
        $q = new \Maps\Model\Floor\Queries\FloorsDatagridQuery($this->getParameter('id'));
        $datasource = new QueryBuilder($q->getQueryBuilder($this->context->floorRepository));
        $datasource->setMapping([
            'id' => 'f.id',
            'floor_number' => 'f.floor_number',
            'name' => 'f.name',
            'plan' => 'plan',
            'metadata' => 'metadata',
            'proposals' => 'proposals',
        ]);
        $grid->setDataSource($datasource);
        
        $grid->addColumn('floor_number', 'Nadzemní podlaží');
        $grid->addColumn('name', 'Jméno');
        $plan = $grid->addColumn('plan', 'Plán');

        $revisionFnc = function($value, $data) {
            if ($value != NULL) {
                return "<span class='label label-success'><i class='icon-ok icon-white'>&nbsp;</i> verze $value</span>";
            } else {
                return "<span class='label label-important'><i class='icon-remove icon-white'>&nbsp;</i></span>";
            }
        };

        $plan->formatCallback[] = $revisionFnc;

        $m = $grid->addColumn('metadata', 'Metadata');
        $m->formatCallback[] = $revisionFnc;
        $grid->addColumn("proposals", "Nové návrhy")->formatCallback[] = function ($value, $data) {
            if ($value > 0) {
                return Html::el("span", ['class' => 'label label-warning'])->setText($value);
            }
            return $value;
        };
        
        $grid->addActionColumn('a', 'Akce');
        $grid->keyName = 'id';
        $grid->addAction('Detail podlaží', 'Floor:default?building='.$this->getParameter('id'));
        $grid['floor_number']->addDefaultSorting('asc');
    }
}
