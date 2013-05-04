<?php

namespace Maps\Components\Forms;

use Nette\Environment;
use Nette\Utils\Html;

/**
 * Adds ability to download and delete previously uploaded file
 *
 * @author Jan -Quinix- Langer
 */
class FileUpload extends \Nette\Forms\Controls\UploadControl {

	private $defaultValue = '';
    private $deleteOption = true;

	public function __construct($label, $form, $name) {
		parent::__construct($label);
		$form->addCheckbox($name . 'deletor', 'Smazat soubor')->setOption('rendered',true);
	}

	public function setDefaultValue($value) {
		$form = $this->getForm();
		$this->defaultValue = $value;
		if ($form->isSubmitted()) {
			if (isset($form[$this->name . 'deletor'])) {
				$control = $form[$this->name . 'deletor'];
				if ($control->value == TRUE) {
					$path = Environment::expand("%wwwDir%/" . $this->defaultValue);
					@unlink($path);
				}
				unset($form[$this->name . 'deletor']);
			}
		}

		return $this;
	}

	public function getControl() {
		$data = parent::getControl();
		$form = $this->getForm();
		if ($this->defaultValue != "") {
			$table = Html::el('div class=file-container');
                        $container = $table->create("span class=file-container-input");
			$container->add($data);
			
			$container = $table->create("span class=file-container-file");
			$path = Environment::expand("%wwwDir%/" . $this->defaultValue);
			if (file_exists($path) && is_file($path)) {
				$finfo = \finfo_file(\finfo_open(\FILEINFO_MIME_TYPE), $path);
				if (\Nette\Utils\Strings::startsWith($finfo, "image")) {
					$link = Html::el('img')->src('/' . $this->defaultValue);
				} else {
					$link = Html::el("a")->href('/' . $this->defaultValue)->setText(\pathinfo($this->defaultValue, \PATHINFO_BASENAME));
				}
				$container->add($link);
			}
                        if(isset($form[$this->name.'deletor'])) {
                            $ch = $form[$this->name . 'deletor'];
                            $container = $table->create("span class=file-container-deletor");
                            $container->add($ch->getLabel());
                            $container->add($ch->getControl());
                        }
		} else {
			unset($form[$this->name . 'deletor']);
			$container = $data;
		}
		return isset($table)?$table:$data;
	}
        
        public function disableDeleteOption($set = true) {
            $this->deleteOption = !$set;
            if($set) {
                $form = $this->getForm(); 
                if(isset($form[$this->name.'deletor'])) {
                    $form->removeComponent($form[$this->name.'deletor']);
                }
            }
        }

}

?>
