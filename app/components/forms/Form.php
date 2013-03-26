<?php
namespace Maps\Components\Forms;
use Nette\Application\UI\Form AS AppForm;
use Maps\Presenter\BasePresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Forms\Form as NForm;
use Nextras\Forms\Controls\OptionList;

/**
 * Description of BaseForm
 *
 * @author Jan -Quinix- Langer
 */

//setup of default rule messages

\Nette\Forms\Rules::$defaultMessages = array(
		NForm::PROTECTION => 'Bezpečnostní kód nesouhlasí, možný CSRF útok. Zkuste odeslat formulář znovu.',
		NForm::EQUAL => 'Prosím vložte %s.',
		NForm::FILLED => 'Pole %label je povinné.',
		NForm::MIN_LENGTH => 'Please enter a value of at least %d characters.',
		NForm::MAX_LENGTH => 'Please enter a value no longer than %d characters.',
		NForm::LENGTH => 'Please enter a value between %d and %d characters long.',
		NForm::EMAIL => 'Please enter a valid email address.',
		NForm::URL => 'Please enter a valid URL.',
		NForm::INTEGER => 'Please enter a numeric value.',
		NForm::FLOAT => 'Please enter a numeric value.',
		NForm::RANGE => 'Please enter a value between %d and %d.',
		NForm::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		NForm::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
	);

class Form extends AppForm {

    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->addProtection();
        $this->setRenderer(new BootstrapRenderer());
    /*    $render=$this->getRenderer();
        $render->wrappers['label']['suffix']=":";
        $render->wrappers['label']['requiredsuffix']="*";
        $render->wrappers['controls']['container'] = 'table width=100%';
        $render->wrappers['label']['container'] = 'th width=200';*/


    }

    public function addError($message) {
        if(trim($message) != "")
            $this->getPresenter()->flashMessage($message, BasePresenter::FLASH_ERROR);
        $this->valid = FALSE;
    }

    public function addDateTimePicker($name, $label, $cols = NULL, $maxLength = NULL) {
        

        return $this[$name] = new \DateTimePicker($label, $cols, $maxLength);
    }

    public function addCKeditor($name, $label = NULL, $cols = 40, $rows = 10) {

        $area = $this[$name] = new CKTextArea($label, $this, $name, $cols, $rows);
        $area->getControlPrototype()->class='ckeditor';
        $area->addFilter(callback("\SeriesCMS\Tools\Mixed::sanitazeCKEditor"));
        
        $this->form->getElementPrototype()->onsubmit[] = 'CKEDITOR.instances["' . $area->getHtmlId() . '"].updateElement()';
        return $area;
    }

    public function addTagInput($name, $label, $items, $useKeys = TRUE) {
        return $this[$name] = new \SuggestPicker\SuggestPicker($label, $items, $useKeys);
    }

	public function addFileUpload($name, $label = NULL)
	{
		return $this[$name] = new FileUpload($label, $this, $name);
	}
	
	public function addCaptcha($name, $label = NULL) {
		return $this[$name] = new \PhraseCaptcha();
	}
        
        public function add3SCheckbox($name, $label = NULL) {
            return $this[$name] = new \SeriesCMS\Components\Forms\CBox3S($label);
        }

    public function addSubmit($name, $caption = NULL) {
        $component = parent::addSubmit($name, $caption);
        $component->getControlPrototype()->addClass("btn-primary");
        return $component;

    }

    public function addOptionList($name, $label = NULL, array $items = NULL) {
        return $this[$name] = new OptionList($label, $items);
    }


}
