<?php
namespace Maps\Presenter;
use Maps\Components\Forms\EntityForm;
use Maps\Model\Persistence\BaseFormProcessor;

/**
 * Class UserPresenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class UserPresenter extends SecuredPresenter {
    /** @var \Maps\Model\Dao */
    private $repository;

    public function startup() {
        parent::startup();
        $this->repository = $this->getContext()->userRepository;
    }

    protected function beforeRender()
    {
        if($this->getView() != 'default') {
            $this->addBreadcrumb('User:','Uživatelé');
        }
        parent::beforeRender();
    }

    /**
     * @param int $id user id
     */
    public function actionEdit($id) {
        $this['form']->bindEntity($this->repository->find($id));
    }

    /**
     * @param int $id user id
     */
    public function handleDelete($id) {
        try {
            $entity = $this->repository->find($id);
            $this->repository->delete($entity);
            $this->flashMessage("Záznam byl úspěšně smazán", self::FLASH_SUCCESS);
        } catch (\Exception $e) {
            $this->flashMessage('Záznam nebyl smazán. ' . $e->getMessage(), self::FLASH_ERROR);
        }
        $this->redirect('default');
    }

    public function createComponentUserGrid($name){
        $grid = new \DataGrid\DataGrid($this, $name);
        $q = new \Maps\Model\BaseDatagridQuery();
        $datasource = new \DataGrid\DataSources\Doctrine\QueryBuilder(
            $q->getQueryBuilder($this->repository)
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
        $form = new EntityForm($this, $name);
        $form->setEntityService(new BaseFormProcessor($this->repository));

        $form->addText("name","Jméno")
            ->setRequired();
        $form->addText("username","ČVUT username")
            ->setRequired();
        $form->addSelect("role","Role",["guest"=>"Host","registred"=>"Registrovaný","admin"=>"Administrátor"])
            ->setRequired();

        $form->addSubmit("send","Odeslat");
        $form->setRedirect("default");
    }
}
