<?php
namespace Maps\Presenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Latte\Macros\MacroSet;
use Nette\Security\Diagnostics\UserPanel;

/**
 * Base presenter for every app presenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz
 */
abstract class BasePresenter extends Presenter {
    /** Flash message type - error */
    const FLASH_ERROR = 'error';
    /** Flash message type - warning */
    const FLASH_WARNING = 'warning';
    /** Flash message type - success */
    const FLASH_SUCCESS = 'success';
    /** @var array  */
    public $breadcrumbs = [['link'=> '//Dashboard:','title'=>'Nástěnka']];

    /** {@inheritdoc} */
    protected function startup() {
        parent::startup();
        new \DebugPanel\PresenterLinkPanel($this);
        if($this->getUser()->isLoggedIn()) {
            $p = \Panel\UserPanel::register();
            $p->addCredentials("admin",'lalalalappp');
            $p->addCredentials('user', 'lalalalappp');
        }
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
            "meta_node" => "Maps\\Model\\Metadata\\Node",
            "meta_path" => "Maps\\Model\\Metadata\\Path",
            "meta_floor_connection" => "Maps\\Model\\Metadata\\FloorConnection"
        ];
        if(isset($aliasMap[strtolower($entity)])) {
            return $this->getContext()->em->getRepository($aliasMap[strtolower($entity)]);
        }
        return $this->getContext()->em->getRepository($entity);
    }
    /** {@inheritdoc} */
    protected function beforeRender() {
        $this->template->breadcrumbs = $this->breadcrumbs;
        parent::beforeRender();
    }

    /**
     * @param string $link presenter:action link
     * @param string $title title
     */
    public function addBreadcrumb($link, $title) {
        $trimed = trim($link, '/ ');
        if(strrpos($trimed, "?") !== FALSE) {
            $trimed = substr($trimed,0,strrpos($trimed, "?")- strlen($trimed));
        }
        $parts = explode(":", $trimed);
        $action = array_pop($parts);
        if($action == "") {
            $action = "default";
        }
        $presenter = implode(":", $parts);

        $this->breadcrumbs[] = ['link'=>($this->getUser()->isAllowed($presenter, $action)?$link:NULL), 'title'=>$title];
    }


}
