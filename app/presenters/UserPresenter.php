<?php
namespace Maps\Presenter;
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 5.2.13
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */
class UserPresenter extends SecuredPresenter {

    public function actionEdit($id) {
        $this['form']->bind($this->getContext()->UserRepository->findBy(['id'=>$id]));
    }

    public function createComponentUserGrid($name){
        $grid = new \DataGrid\DataGrid($this, $name);
        $q = new \Maps\Model\BaseDatagridQuery();
        $datasource = new \DataGrid\DataSources\Doctrine\QueryBuilder(
            $q->getQueryBuilder($this->getContext()->em->getRepository('Maps\Model\User\User'))
        );
        $datasource->setMapping([
            "id"=>"b.id", "name"=>"b.name", "username"=>"b.username"
        ]);


        $grid->setDataSource($datasource);

        $grid->addColumn("id","ID#");
        $grid->addColumn("name","Jméno")
            ->addFilter();
        $grid->addColumn("username","Uživatel")
            ->addFilter();

        $grid['id']->addDefaultSorting('asc');
        $grid->keyName = "id";
        $grid->addActionColumn("a","Akce");

        $grid->addAction("Upravit", "edit");
        $grid->addAction("Smazat","delete!")
            ->addConfirmation("Opravdu?");

        $grid->itemsPerPage = 10;
    }

    public function createComponentForm($name) {
        $form = new \Components\Forms\EntityForm($this, $name);
        $form->setRepository($this->getContext()->UserRepository);

        $form->addText("name","Jméno")
            ->setRequired();
        $form->addText("username","ČVUT username")
            ->setRequired();
        $form->addSelect("role","Role",["guest"=>"Host","registred"=>"Registrovaný","admin"=>"Administrátor"])
            ->setRequired();

        $form->addSubmit("send","Odeslat");
    }
}
