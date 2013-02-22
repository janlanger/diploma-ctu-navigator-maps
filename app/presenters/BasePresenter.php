<?php
namespace Maps\Presenter;
use Nette\Application\UI\Presenter;
/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_SUCCESS = 'success';

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
            "plan" => "Maps\\Model\\FloorPlan\\FloorPlan",
            "plannode" => "Maps\\Model\\FloorPlan\\Node"
        ];
        if(isset($aliasMap[strtolower($entity)])) {
            return $this->getContext()->em->getRepository($aliasMap[strtolower($entity)]);
        }
        return $this->getContext()->em->getRepository($entity);
    }


}
