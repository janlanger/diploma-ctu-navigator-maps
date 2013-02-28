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
use Maps\Model\Floor\PlanRevisionsQuery;

class PlanPresenter extends SecuredPresenter {

    public function actionDefault($id) {
        $floor = $this->getRepository('floor')->find($id);
        $this->template->floor = $floor;
        $this->template->building = $floor->building;
    }

    public function createComponentGrid($name) {
        $floor = $this->template->floor;

        $q = new PlanRevisionsQuery($floor->id);
        $datasource = new QueryBuilder($q->getQueryBuilder($this->getRepository('plan')));
        $datasource->setMapping([
            'id' => 'p.id',
            'revision' => 'p.revision',
            'published' => 'p.published',
        ]);


        $grid = new DataGrid($this, $name);
        $grid->setDataSource($datasource);

        $grid->addColumn('revision','Revize')->addDefaultSorting('desc');
        $grid->addColumn('published','Aktivní');

        $grid->addColumn('user','Nahrál');
        $grid->addDateColumn('added','Nahráno');
        $grid->addDateColumn('published_date','Publikováno');
    }

}