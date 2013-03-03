<?php
namespace Maps\Presenter;
use Maps\Model\Building\BuildingFormProcessor;
use DataGrid\DataSources\Doctrine\QueryBuilder;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.2.13
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */
class BuildingPresenter extends SecuredPresenter {

    public function actionDetail($id) {
        $this->template->building = $this->getRepository("building")->find($id);
    }

    public function actionAdd() {
        $this['form']->bindEntity($this->getRepository("building")->createNew());
    }

    public function actionEdit($id) {
        $this['form']->bindEntity($this->getRepository('building')->find($id));
    }

    public function handleDelete($id) {
        try {
            $entity = $this->getRepository('building')->find($id);
            $this->getRepository('building')->delete($entity);
            $this->flashMessage("Záznam byl úspěšně smazán", self::FLASH_SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage('Záznam nebyl smazán. ' . $e->getMessage(), self::FLASH_ERROR);
        }
        $this->redirect('default');
    }

    public function actionKosApi($id) {
        $this->template->building = $this->getRepository('building')->find($id);
        $this->template->rooms = $this->getRepository('room')->findBy(['building'=>$id]);
        $q = new \Maps\Model\KosApi\LastCommunicationQuery();
        $log = $q->fetch($this->getRepository('kosapiLog'), \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if(!empty($log)) {
            $this->template->log = $log;
        }
    }

    public function createComponentBuildingsGrid($name) {
        $grid = new \DataGrid\DataGrid($this, $name);
        $query = new \Maps\Model\BaseDatagridQuery();
        $ds = new QueryBuilder($query->getQueryBuilder($this->getContext()->em->getRepository('Maps\Model\Building\Building')));
        $ds->setMapping([
            'id'=>'b.id',
            'name'=>'b.name',
            'address'=>'b.address',
        ]);

        $grid->setDataSource($ds);

        $grid->addColumn("id","ID#");
        $grid->addColumn("name","Budova")->addFilter();
        $grid->addColumn("address","Adresa")->addFilter();

        $grid['id']->addDefaultSorting('asc');
        $grid->keyName = "id";
        $grid->addActionColumn("a","Akce");
        $grid->addAction("Detail", "Building:detail");
    }



    private function googleMapBase($name) {
        $map = new \Maps\Components\GoogleMaps\BasicMap($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        return $map;
    }

    /**
     * @return \Maps\Components\GoogleMaps\BasicMap
     */
    protected function createComponentGoogleMap($name) {
       $map = $this->googleMapBase($name);

        /** @var $entity \Maps\Model\Building\Building */
        $entity = $this->getRepository("building")->find($this->getParameter('id'));
        if($entity->getGpsCoordinates() != null) {
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
        $form->setEntityService(new BuildingFormProcessor($this->getRepository('building')));

        $form->addText('name','Název')
            ->setRequired();
        $form->addText('address','Adresa')
            ->setRequired();
        $form->addText('floorCount','Počet podlaží')
            ->addRule(\Maps\Components\Forms\Form::NUMERIC)
            ->setRequired();
        $form->addText('roomPrefix','Prefix místností', null, 10)
            ->setRequired();
        $form->addText('gpsCoordinates', "GPS souřadnice")
            ->setRequired();
        $form->addSubmit('send','Odeslat');
        $id = $this->getParameter('id');
        if($id == null) {
            $form->setRedirect("default");
        } else {
            $form->setRedirect("detail?id=".$id);
        }
    }
    
    public function createComponentPlansGrid($name) {
        $grid = new \DataGrid\DataGrid($this, $name);
        $q = new \Maps\Model\Floor\PlanDatagridQuery($this->getParameter('id'));
        $datasource = new QueryBuilder($q->getQueryBuilder($this->getRepository('floor')));
        $datasource->setMapping([
            'id' => 'b.id',
            'floor_number' => 'b.floor_number',
            'name' => 'b.name',
            'version' => 'b.version',
        ]);
        $grid->setDataSource($datasource);
        
        $grid->addColumn('floor_number', 'Podlaží');
        $grid->addColumn('name', 'Jméno');
        $grid->addColumn('version', 'Verze');
    //    $grid->addCheckboxColumn('actual_version','Pouze poslední');
        
        $grid->addActionColumn('a', 'Akce');
        $grid->keyName = 'id';
        $grid->addAction('Základní data', 'Floor:default');

        $grid->addAction('Plán', 'Plan:default')->key = 'floor';
        $grid->addAction('Metadata', 'Metadata:default');
        
        $grid->keyName = 'id';
        $grid['floor_number']->addDefaultSorting('asc');
    }
}
