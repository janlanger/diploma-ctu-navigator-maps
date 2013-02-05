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
        $grid = new \Grido\Grid($this, $name);

        $grid->setModel($this->getContext()->UserRepository->getGridDatasource());

        $grid->addColumn("id","ID#");
        $grid->addColumn("name","Jméno")
            ->setFilter()
            ->setSuggestion();
        $grid->addColumn("username","Uživatel")
            ->setFilter()
                ->setSuggestion();

        $grid->addAction("edit","Upravit");
        $grid->addAction("delete","Smazat")
        ->setConfirm("Opravdu?");

        $grid->setDefaultPerPage(10);
        $grid->setDefaultSort(['id'=>"asc"]);
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
