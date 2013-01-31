<?php

namespace Components\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Forms\IControl;

/**
 * Description of DefaultRenderer
 *
 * @author Jan -Quinix- Langer
 */
class DefaultRenderer extends DefaultFormRenderer {

    /**
	 * Renders 'label' part of visual row of controls.
	 * @param  IControl
	 * @return string
	 */
	public function renderLabel(IControl $control)
	{
		$head = $this->getWrapper('label container');

		if($control instanceof \Nette\Forms\Controls\Button) {
            return '';
        }
		/*if ($control instanceof Checkbox || $control instanceof Button) {
			return $head->setHtml(($head->getName() === 'td' || $head->getName() === 'th') ? '&nbsp;' : '');

		} else*/ {
			$label = $control->getLabel();
			$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
			if ($label instanceof Html) {
				$label->setHtml($label->getHtml() . $suffix);
				$suffix = '';
			}
			return $head->setHtml((string) $label . $suffix);
		}
	}



	/**
	 * Renders 'control' part of visual row of controls.
	 * @param  IControl
	 * @return string
	 */
	public function renderControl(IControl $control)
	{
		$body = $this->getWrapper('control container');
		if ($this->counter % 2) $body->class($this->getValue('control .odd'), TRUE);

		$description = $control->getOption('description');
		if ($description instanceof Html) {
			$description = ' ' . $control->getOption('description');

		} elseif (is_string($description)) {
			$description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));

		} else {
			$description = '';
		}

		if ($control->isRequired()) {
			$description = $this->getValue('control requiredsuffix') . $description;
		}

		if ($this->getValue('control errors')) {
			$description .= $this->renderErrors($control);
		}

		if (/*$control instanceof Checkbox || */$control instanceof Button) {
			return $body->setHtml((string) $control->getControl() . (string) $control->getLabel() . $description);

		} else {
			return $body->setHtml((string) $control->getControl() . $description);
		}
	}

    public function renderPair(IControl $control) {
        if ($control instanceof CKTextArea) {
            $pair = $this->getWrapper('pair container');

            $pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
            $pair->class($control->getOption('class'), TRUE);
            if (++$this->counter % 2)
                $pair->class($this->getValue('pair .odd'), TRUE);
            $pair->id = $control->getOption('id');


            $pair2 = clone $pair;

            $pair->add($this->renderLabel($control)->colspan(2));
            $pair2->add($this->renderControl($control)->colspan(2));
            return $pair->render(0). $pair2->render(0);
        }
        return parent::renderPair($control);
    }



}
