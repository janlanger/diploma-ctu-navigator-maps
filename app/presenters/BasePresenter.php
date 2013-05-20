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
    public $breadcrumbs = [];

    /** {@inheritdoc} */
    protected function startup() {
        parent::startup();
        if(!$this->user->isInRole('admin')) {
            $this->addBreadcrumb('//Dashboard:', 'Nástěnka');
        }
        new \DebugPanel\PresenterLinkPanel($this);
        if($this->getUser()->isLoggedIn()) {
            $p = \Panel\UserPanel::register();
            $p->addCredentials("admin",'lalalalappp');
            $p->addCredentials('user', 'lalalalappp');
        }
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
