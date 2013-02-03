<?php

use Nette\Security\User;

/**
 * Description of BasePresenter
 *
 * @author Jan -Quinix- Langer
 */
abstract class SecuredPresenter extends BasePresenter {

    public function startup() {
        parent::startup();
        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            if ($user->getLogoutReason() === User::INACTIVITY) {
                $this->flashMessage('Vaše sezení vypršelo. Přihlašte se prosím znovu, původní požadavek bude poté obnoven.', self::FLASH_ERROR);
            }

            $backlink = $this->getApplication()->storeRequest();
            $this->redirect('Sign:in', array('backlink' => $backlink));
        } else {
/*            if(!$user->isAllowed('Admin:Dashboard','default')) {
                $this->flashMessage('Nemáte potřebná oprávnění.', self::FLASH_ERROR);
                $this->redirect(':Front:News:latest');
            }*/
            if (!$user->isAllowed($this->getName(), $this->getAction())) {
                $this->flashMessage('Přístup odepřen!', 'error');
                $this->redirect('Homepage:');
            }
        }
    }

}
