<?php
namespace Maps\Components\GoogleMaps;
use Maps\Components\Forms\Form;

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

    public function bindedFormField(\Nette\Forms\IControl $control) {
        $this->formField = $control;
    }

    public function setSubmitButton(\Nette\Forms\IControl $control) {
        $this->submit = $control;
    }


    public function render() {

        $template = $this->createTemplate();

        $template->setFile(__DIR__.'/templates/polyLinesEditor.latte');

        $this->setMapSize($template, func_get_args());
        $template->textField = $this->formField;
        $template->submit = $this->submit;

        $template->render();
    }

    public function createComponentForm($name) {
        $form = new \Maps\Components\Forms\Form($this, $name);

        $form->addSelect('type','Typ',[
            'intersection' => 'Křižovatka',
            'entrance' => 'Vstup',
            'elevator' => 'Výtah',
            'stairs' => 'Schodiště',
            'passage' => 'Průchod (do jiné budovy)',
            'lecture' => 'Učebna',
            'auditorium' => 'Posluchárna',
            'office' => 'Kancelář',
            'study' => 'Studovna',
            'cafeteria' => 'Kantýna',
            'restroom-men' => 'WC muži',
            'restroom-woman' => 'WC ženy',
            'cloakroom' => 'Šatna',
        ])
            ->setPrompt('-- Typ --');

        $form->addText('name','Název');
        $form->addText('room','Číslo místnosti'); //TODO: suggest input
        $form->addText('fromFloor','Z podlaží (nejnižší)'); //TODO: select ze známých pater
        $form->addText('toFloor','Do podlaží');

        $form->addSelect('toBuilding','Do budovy')
            ->setPrompt('-- Do budovy --'); //TODO: číselník budov
        $form->addButton('save','Uložit');
        $form->addButton('delete','Odstranit bod');

    }
}
