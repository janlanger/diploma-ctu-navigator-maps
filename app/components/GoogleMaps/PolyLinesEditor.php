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


    public function render() {

        $template = $this->createTemplate();

        $template->setFile(__DIR__.'/templates/polyLinesEditor.latte');

        $this->setMapSize($template, func_get_args());

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
            ->setPrompt('-- Typ --')
            ->addCondition(~Form::IS_IN, ['','intersection','elevator','stairs','passage','restroom-men','restroom-woman','cloakroom'])
                ->toggle('form-name')
            ->addCondition(Form::IS_IN, ['lecture','auditorium','office','study'])
                ->toggle('form-room')
            ->addCondition(Form::IS_IN, ['elevator'])
                ->toggle('form-fromFloor')
            ->addCondition(Form::IS_IN, ['elevator', 'stairs', 'passage'])
                ->toggle('form-toFloor')
            ->addCondition(Form::IS_IN, ['passage'])
                ->toggle('form-toBuilding');

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
