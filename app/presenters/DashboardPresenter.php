<?php
namespace Maps\Presenter;
use DataGrid\DataSources\Doctrine\QueryBuilder;
/**
 * Dashboard presenter.
 */
class DashboardPresenter extends SecuredPresenter
{

    public function actionDefault() {
        $this->redirect("Building:default");
        $this->terminate();
    }

}
