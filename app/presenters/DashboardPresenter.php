<?php
namespace Maps\Presenter;
use Maps\Model\Building;
use DataGrid\DataSources\Doctrine\QueryBuilder;
/**
 * Dashboard presenter.
 */
class DashboardPresenter extends SecuredPresenter
{

    public function createComponentBuildingsGrid($name) {
        $grid = new \DataGrid\DataGrid($this, $name);
        $query = new Building\DatagridQuery();
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

}
