<?php
namespace Maps\Components\Forms;
use DependentSelectBox\JsonDependentSelectBox;
use Nette\Application\UI\Form AS AppForm;
use Maps\Presenter\BasePresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Forms\Form as NForm;
use Nette\Forms\IControl;
use Nextras\Forms\Controls\OptionList;

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
/**
 * Base form for every form in app. Adds extended field type to forms
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 * @package Maps\Components\Forms
 */

class Form extends AppForm {

    /**
     * @inheritdoc
     */
    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->addProtection();
        $this->setRenderer(new BootstrapRenderer());
    }

    /**
     * @param string $message adds form error as flash message
     */
    public function addError($message) {
        if(trim($message) != "")
            $this->getPresenter()->flashMessage($message, BasePresenter::FLASH_ERROR);
        $this->valid = FALSE;
    }

    /**
     * Creates date time picker field
     *
     * @param string $name field internal name
     * @param string $label field label
     * @param string $cols
     * @param string $maxLength
     * @return \DateTimePicker
     */
    public function addDateTimePicker($name, $label, $cols = NULL, $maxLength = NULL) {
        return $this[$name] = new \DateTimePicker($label, $cols, $maxLength);
    }

    /**
     * Adds suggested (autocomplete) input
     *
     * @param $name
     * @param $label
     * @param $items
     * @param bool $useKeys
     * @return \SuggestPicker\SuggestPicker
     */
    public function addTagInput($name, $label, $items, $useKeys = TRUE) {
        return $this[$name] = new \SuggestPicker\SuggestPicker($label, $items, $useKeys);
    }

    /**
     * Added extended file upload field
     *
     * @param $name
     * @param null $label
     * @return FileUpload
     */
    public function addFileUpload($name, $label = NULL)
	{
		return $this[$name] = new FileUpload($label, $this, $name);
	}

    /**
     * Adds 3-state checkbox field
     *
     * @param string $name
     * @param string $label
     * @return CBox3S
     */
    public function add3SCheckbox($name, $label = NULL) {
        return $this[$name] = new CBox3S($label);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubmit($name, $caption = NULL) {
        $component = parent::addSubmit($name, $caption);
        $component->getControlPrototype()->addClass("btn-primary");
        return $component;

    }

    /**
     * Adds options list field container
     *
     * @param string $name
     * @param string $label
     * @param array $items
     * @return OptionList
     */
    public function addOptionList($name, $label = NULL, array $items = NULL) {
        return $this[$name] = new OptionList($label, $items);
    }

    /**
     * @param string $name
     * @param string $caption
     * @param IControl $parent root element
     * @param $callback root element changed event
     * @param bool $autoselect execute autoselect of first item in root element?
     * @return JsonDependentSelectBox
     */
    public function addDependedSelect($name, $caption, $parent, $callback, $autoselect = TRUE) {
        $s = new JsonDependentSelectBox($caption, $parent, $callback);
        $s->autoSelectRootFirstItem = $autoselect;
        return $this[$name] = $s;
    }


}
