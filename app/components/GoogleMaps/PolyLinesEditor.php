<?php
namespace Maps\Components\GoogleMaps;
use Maps\Components\Forms\Form;
use Maps\Model\Building\DictionaryQuery;
use Maps\Presenter\BasePresenter;

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 10.2.13
 * Time: 22:25
 * To change this template use File | Settings | File Templates.
 */
class PolyLinesEditor extends BaseMapControl {

    private $formField;
    private $submit;
    private $roomPrefix;
    private $overiden;



    private $buildings=[];

    public function bindFormField(\Nette\Forms\IControl $control) {
        $this->formField = $control;
    }



    public function setOveriden($overiden) {
        $this->overiden = $overiden;
    }



    public function getOveriden() {
        return $this->overiden;
    }



    public function setSubmitButton(\Nette\Forms\IControl $control) {
        $this->submit = $control;
    }

    public function setRoomPrefix($roomPrefix)
    {
        $this->roomPrefix = $roomPrefix;
    }



    public function render() {

        $template = $this->createTemplate();

        $template->setFile(__DIR__.'/templates/polyLinesEditor.latte');

        $this->setMapSize($template, func_get_args());
        $template->textField = $this->formField;
        $template->submit = $this->submit;
        $template->roomPrefix = $this->roomPrefix;
        $template->overiden = $this->overiden;

        $template->render();
    }

    public function createComponentForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $types = [];
        foreach($this->getNodeTypes() as $type=>$title) {
            $types[$type] = $title['legend'];
        }

        $form->addSelect('type','Typ', $types)
            ->setPrompt('-- Typ --');

        $form->addText('name','Název');
        $form->addText('room','Číslo místnosti');
        $form->addHidden('otherNode', null);

        $form->addButton('save','Uložit');
        $form->addButton('delete','Odstranit bod');

    }

    public function setBuildingsDictionary($buildings)
    {
        $this->buildings = $buildings;
    }
}
