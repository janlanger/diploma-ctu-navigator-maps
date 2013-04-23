<?php
namespace Maps\Presenter;
use Maps\Components\Forms\Form;
use Nette\Application\UI;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

    public function actionIn($key) {
        if ($this->getUser()->isLoggedIn()) {
            if ($key) {
                $this->restoreRequest($key);
            }
            $this->redirect("Dashboard:");
        }
    }

    /**
     * Sign-in form factory.
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Login');

        $form->addPassword('password', 'Heslo')->setOption("description", "Ověřování proti fakultnímu LDAP.");

        $form->addCheckbox('remember', 'Trvalé přihlášení');

        $form->addSubmit('send', 'Přihlásit');


        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->signInFormSucceeded;
        return $form;
    }


    public function signInFormSucceeded($form) {
        $values = $form->getValues();
        $backlink = $this->getParameter('key');

        if ($values->remember) {
            $this->getUser()->setExpiration('+ 14 days', FALSE);
        }
        else {
            $this->getUser()->setExpiration('+ 20 minutes', TRUE);
        }

        try {
            $this->getUser()->login($values->username, $values->password);
            if (isset($backlink) && !is_null($backlink)) {
                $this->restoreRequest($backlink);
            }
            $this->redirect("Dashboard:");
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }
    }


    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

}
