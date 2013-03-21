<?php
namespace Maps\Presenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Latte\Macros\MacroSet;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_SUCCESS = 'success';

    public $breadcrumbs = [['link'=> '//Dashboard:','title'=>'Nástěnka']];

    protected function startup() {
        parent::startup();
        new \DebugPanel\PresenterLinkPanel($this);
    }

    public function formatTemplateFiles() {
        return parent::formatTemplateFiles();
    }

    /**
     * @param $entity string entity FQ name or known alias
     * @return \Maps\Model\Dao entity repository
     */
    protected function getRepository($entity) {
        $aliasMap = [
            "user" => "Maps\\Model\\User\\User",
            "building" => "Maps\\Model\\Building\\Building",
            "aclrole" => "Maps\\Model\\ACL\\Role",
            "aclprivilege" => "Maps\\Model\\ACL\\Privilege",
            "aclresource" => "Maps\\Model\\ACL\\Resource",
            "acl" => "Maps\\Model\\ACL\\ACL",
            "floor" => "Maps\\Model\\Floor\\Floor",
            "plan" => "Maps\\Model\\Floor\\Plan",
            "meta_revision" => "Maps\\Model\\Metadata\\Revision",
            "meta_node_properties" => "Maps\\Model\\Metadata\\NodeProperties",
            "meta_path_properties" => "Maps\\Model\\Metadata\\PathProperties",
            "meta_changeset" => "Maps\\Model\\Metadata\\Changeset",
            "meta_node_change" => "Maps\\Model\\Metadata\\NodeChange",
            "meta_path_change" => "Maps\\Model\\Metadata\\PathChange",
        ];
        if(isset($aliasMap[strtolower($entity)])) {
            return $this->getContext()->em->getRepository($aliasMap[strtolower($entity)]);
        }
        return $this->getContext()->em->getRepository($entity);
    }

    protected function beforeRender() {
        $this->template->breadcrumbs = $this->breadcrumbs;
        parent::beforeRender();
    }


    public function addBreadcrumb($link, $title) {
        $this->breadcrumbs[] = ['link'=>$link, 'title'=>$title];
    }


}
