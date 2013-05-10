<?php
namespace Maps\Presenter;
use Nette\Security\User;

/**
 * Provides authorization on every request
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
abstract class SecuredPresenter extends BasePresenter {
    /**
     * Checks if user role is authorized to perform requested action
     */
    public function startup() {
        parent::startup();
        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            if ($user->getLogoutReason() === User::INACTIVITY) {
                $this->flashMessage('Vaše sezení vypršelo. Přihlašte se prosím znovu, původní požadavek bude poté obnoven.', self::FLASH_ERROR);
            }

            $backlink = $this->storeRequest();
            $this->redirect('Sign:in', array('key' => $backlink));
        } else {
/*            if(!$user->isAllowed('Admin:Dashboard','default')) {
                $this->flashMessage('Nemáte potřebná oprávnění.', self::FLASH_ERROR);
                $this->redirect(':Front:News:latest');
            }*/
            if (!$user->isAllowed($this->getName(), $this->getAction())) {
                $this->flashMessage('Přístup odepřen!', 'error');
                $this->redirect('Dashboard:');
            }
        }
    }

}
