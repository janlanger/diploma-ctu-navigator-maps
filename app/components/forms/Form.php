<?php
namespace Maps\Components\Forms;
use DependentSelectBox\JsonDependentSelectBox;
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
		NForm::PROTECTION => 'Došlo k chybě při odesílání formuláře. Zkuste to prosím znovu.',
		NForm::EQUAL => 'Prosím vložte %s.',
		NForm::FILLED => 'Vyplňte prosím pole %label.',
		NForm::MIN_LENGTH => 'Do pole %label vyplňte prosím alespoň %d znaků.',
		NForm::MAX_LENGTH => 'Prosím vyplňte nejvýše %d znaků do pole $label.',
		NForm::LENGTH => 'Do pole %label vyplňte minimálně %d a maximálně %d znaků.',
		NForm::EMAIL => 'Zkontrolujte prosím e-mailovou adresu v poli %label.',
		NForm::URL => 'Do pole %label zadejte prosím URL adresu ve správném formátu.',
		NForm::INTEGER => 'Do pole %label prosím zadejte celočíselnou hodnotu.',
		NForm::FLOAT => 'Do pole %label prosím zadejte číselnou hodnotu.',
		NForm::RANGE => 'Do pole %label prosím vyplňte číslo mezi %d a %d.',
		NForm::MAX_FILE_SIZE => 'Maximální velikost nahraného souboru může být %d bytů.',
		NForm::IMAGE => 'Nahraný soubor musí být obrázek ve formátu JPEG, GIF nebo PNG.',
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
            return $this[$name] = new CBox3S($label);
        }

    public function addSubmit($name, $caption = NULL) {
        $component = parent::addSubmit($name, $caption);
        $component->getControlPrototype()->addClass("btn-primary");
        return $component;

    }

    public function addOptionList($name, $label = NULL, array $items = NULL) {
        return $this[$name] = new OptionList($label, $items);
    }

    /**
     * @param $name
     * @param $caption
     * @param $parent
     * @param $callback
     * @return JsonDependentSelectBox
     */
    public function addDependedSelect($name, $caption, $parent, $callback, $autoselect = TRUE) {
        $s = new JsonDependentSelectBox($caption, $parent, $callback);
        $s->autoSelectRootFirstItem = $autoselect;
        return $this[$name] = $s;
    }


}
