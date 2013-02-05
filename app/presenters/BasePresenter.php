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

}
