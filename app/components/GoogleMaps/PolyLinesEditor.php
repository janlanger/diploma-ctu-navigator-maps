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
    private $types;
    private $nodeIconBase;
    private $roomPrefix;



    private $buildings=[];

    public function bindFormField(\Nette\Forms\IControl $control) {
        $this->formField = $control;
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
        $template->nodeTypes = $this->types;
        $template->iconsBasePath = $this->nodeIconBase;
        $template->roomPrefix = $this->roomPrefix;

        $template->render();
    }

    public function setNodeTypes(array $types) {
        $this->types= $types;
    }

    public function setNodeIconBase($nodeIconBase)
    {
        $this->nodeIconBase = $nodeIconBase;
    }



    public function createComponentForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);
        $types = [];
        foreach($this->types as $type=>$title) {
            $types[$type] = $title['legend'];
        }

        $form->addSelect('type','Typ', $types)
            ->setPrompt('-- Typ --');

        $form->addText('name','Název');
        $form->addText('room','Číslo místnosti'); //TODO: suggest input
        $form->addText('fromFloor','Z podlaží (nejnižší)'); //TODO: select ze známých pater
        $form->addText('toFloor','Do podlaží');
        $form->addSelect('toBuilding','Do budovy', $this->buildings)
            ->setPrompt('-- Do budovy --');
        $form->addButton('save','Uložit');
        $form->addButton('delete','Odstranit bod');

    }

    public function setBuildingsDictionary($buildings)
    {
        $this->buildings = $buildings;
    }
}
