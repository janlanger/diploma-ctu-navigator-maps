<?php

/**
 * Dashboard presenter.
 */
class DashboardPresenter extends SecuredPresenter
{

    public function createComponentBuildingsGrid($name) {
        $grid = new \Grido\Grid($this, $name);
        $grid->setModel($this->getContext()->BuildingRepository->getGridDatasource());

        $grid->addColumn("id","ID#")
            ->setSortable()
            ->setFilter();
        $grid->addColumn("name","Budova")
            ->setSortable()
            ->setFilter()
                ->setSuggestion();
        $grid->addColumn("address","Adresa")
            ->setSortable();

        $grid->setDefaultPerPage(10);
        $grid->addAction('detail',"Detail", "Building:detail");

        $grid->setDefaultSort(['id'=>"asc"]);
    }

}
