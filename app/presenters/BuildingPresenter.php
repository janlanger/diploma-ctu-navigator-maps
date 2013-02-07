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

    public function actionEdit($id) {
        $this['form']->bindEntity($this->getRepository('building')->find($id));
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
        $map = new \Maps\Components\GoogleMaps($this, $name);
        $map->setApikey($this->getContext()->parameters['google']['apiKey']);
        return $map;
    }

    /**
     * @return \Maps\Components\GoogleMaps
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
        $form->setRedirect("detail?id=".$this->getParameter('id'));
    }
}
