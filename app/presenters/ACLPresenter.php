<?php

namespace Maps\Presenter;

use Maps\Components\Forms\Form;
use Maps\Components\Forms\EntityForm;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ACLPresenter
 *
 * @author Honza
 */
class ACLPresenter extends SecuredPresenter {

    private $presenters;
    private $actions;
    private $presenterActionMap;
    /** @var \Maps\Model\Dao */
    private $repository;

    public function startup() {
        parent::startup();
        $this->repository = $this->getContext()->em->getRepository('Maps\Model\Acl\Acl');
    }
    
    public function actionAdd($id) {
        $this['editForm']->bindEntity($this->getContext()->ACLService->createBlank());
        $this['editForm']->setSuccessFlashMessage('Role byla úspěšně vytvořena, nastavte jí oprávnění.');
    }

    public function actionEdit($id) {
        $this['editForm']->bindEntity($this->getContext()->ACLService->find($id));
        $this['editForm']->setSuccessFlashMessage('Data byla úspěšně uložena.');
    }
    
    public function handleDelete($id) {
            try {
                $entity = $this->getContext()->ACLService->find($id);
                $this->getContext()->ACLService->delete($entity);
                $this->flashMessage('Role byla úspěšně smazána.', self::FLASH_SUCCESS);

            } catch (\ModelException $e) {
                $this->flashMessage('Role nebyla smazána. '.$e->getMessage(), self::FLASH_ERROR);
            }
            $this->redirect('default');
    }

    public function actionSetup($id) {
        if($id == null || !is_numeric($id)) {
            throw new \Nette\Application\BadRequestException('Missing or invalid parameter format.');
        }
        $roleEnt = $this->getContext()->ACLService->find($id);
        if($roleEnt == NULL) {
            throw new \Nette\Application\BadRequestException('Role ID '.$id.' not found.');
        }
        $tree = $this->getContext()->presenterTree;
        $p = $tree->getPresenters();
        $presenters = array();
        $actions = array();
        $presenterActionMap = array();
        ksort($p);
        foreach ($p as $presente) {
            if ($presente->getPresenterReflection()->isSubclassOf("Maps\\Presenter\\SecuredPresenter")) {
                $pName = ($presente->getModule()!=null?$presente->getModule().":":""). $presente->getName();
                $pActions = ($presente->getActions());

                $presenters[] = $pName;


                foreach ($pActions as $a) {
                    $a = str_replace($pName, "", $a);
                    $actions[] = $a;
                    $presenterActionMap[$pName][$a] = $a;
                }
            }
        }
        sort($actions);
        $this->template->role = $roleEnt->getName();
        $this->template->presenters = $this->presenters = $presenters;
        $this->template->actions = $this->actions = (array_unique($actions));
        $this->template->presenterActionMap = $this->presenterActionMap = ($presenterActionMap);
    }

    public function createComponentRolesGrid($name) {
        $q = new \Maps\Model\ACL\DatagridQuery();
        $dataSource = new \DataGrid\DataSources\Doctrine\QueryBuilder($q->getQueryBuilder($this->getContext()->em->getRepository('Maps\Model\Acl\Role')));
        $dataSource->setMapping(array('id' => 'r.id',
            'name' => 'r.name',
            'parent' => 'name'));

        $grid = new \DataGrid\DataGrid($this, $name);
        $grid->setDataSource($dataSource);
        $grid->addNumericColumn('id', 'ID#');
        $grid->addColumn('name', 'Role');
        $grid->addColumn('parent', 'Nadřazená role');
        $grid->keyName = 'id';
        $grid['id']->addDefaultSorting('asc');

        $grid->addActionColumn('actions', 'Akce');
        $grid->addAction('Upravit', "edit");
        $grid->addAction('Nastavit oprávnění', "setup");
        $action = $grid->addAction('Smazat', "delete!");
        $action->getHtml()->class = 'confirm';
    }

    public function createComponentEditForm($name) {
        $form = new EntityForm($this, $name);
        $form->addText('name', 'Jméno role')->setRequired();
        $form->setEntityService($this->getContext()->ACLService);
        $form->addSelect('parent', 'Nadřazená role', $this->getContext()->ACLService->getDictionary())
                ->setPrompt('--vyberte--');
        $form->setRedirect('default');
        $form->addSubmit('ok', 'Odeslat');

    }

    public function createComponentSetupForm($name) {
        $id = $this->getParam('id');

        $form = new Form($this, $name);
        foreach ($this->presenters as $pr) {
            $p = str_replace(':', "_", $pr);
            $form->add3SCheckbox($p . '__all');
            if (isset($this->presenterActionMap[$pr])) {
                foreach ($this->presenterActionMap[$pr] as $map) {
                    $form->add3SCheckbox($p . '__' . $map);
                }
            }
        }
        $form->addSubmit('ok', 'Odeslat');

        $role = $this->getContext()->ACLService->getRules($id);
        foreach ($role as $item) {
           
                $p = str_replace(':', "_", $item['resource']);
                if ($item['privilege'] == NULL) {
                    $item['privilege'] = 'all';
                }
                if(isset($form[$p . '__' . $item['privilege']])) {
                    $form[$p . '__' . $item['privilege']]->setDefaultValue($item['allowed'] == 1?1:-1);
                }
            
        }
        $form->addHidden('id',$id);
        $form->onSuccess[] = callback($this, 'processSetupForm');
    }
    
    public function processSetupForm(Form $form) {
        $values = $form->getValues();
        
        $toInsert = array();
        foreach($values as $key => $value) {
            $k = (explode("__", $key));
            if(count($k)>1) {
                $k[0] = str_replace("_", ":", $k[0]);
                if($value != 0) {
                    $toInsert[] = array(
                        'resource' => $k[0],
                        'privilege' => ($k[1] == 'all'?null:$k[1]),
                        'allowed' => ($value>0?1:-1),                
                    );
                }
            }
        }
        if($this->getContext()->ACLService->updateRolePrivilege($this->getParam('id'), $toInsert)) {
            $this->flashMessage('Práva byla úspěšně upravena.', self::FLASH_SUCCESS);

            $this->redirect('default');
        } else {
            $this->flashMessage('Při úpravě oprávnění došlo k neočekávané chybě.', self::FLASH_ERROR);
        }
        
        
    }
    

}

?>
