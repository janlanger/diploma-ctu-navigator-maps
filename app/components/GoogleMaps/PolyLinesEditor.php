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

    private $floorExchangePaths;



    private $buildings=[];


    public function setFloorExchangePaths($floorExchangePaths) {
        $this->floorExchangePaths = $floorExchangePaths;
    }

    public function getFloorExchangePaths() {
        return $this->floorExchangePaths;
    }

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

        $starting = [];
        $ending = [];
        if (!empty($this->floorExchangePaths)) {


            foreach ($this->floorExchangePaths as $path) {


                foreach ($this->points as $id => $point) {
                    $node = NULL;
                    if ($path->properties->startNode->id == $point['appOptions']['propertyId']) {
                        $starting[$path->properties->startNode->id][] = [
                            'destinationNode' => $path->properties->endNode->id,
                            'pathId' => $path->properties->id,
                            'destinationFloor' => ['id' => $point['appOptions']['toFloor']->id, 'name' => $point['appOptions']['toFloor']->readableName],
                            'destinationBuilding' => ['id' => $point['appOptions']['toFloor']->building->id, 'name' => $point['appOptions']['toFloor']->building->name]
                        ];
                    }
                    if ($path->properties->endNode->id == $point['appOptions']['propertyId']) {
                        $ending[$path->properties->endNode->id][] = [
                            'destinationNode' => $path->properties->startNode->id,
                            'pathId' => $path->properties->id,
                            'destinationFloor' => ['id' => $point['appOptions']['toFloor']->id, 'name' => $point['appOptions']['toFloor']->readableName],
                            'destinationBuilding' => ['id' => $point['appOptions']['toFloor']->building->id, 'name' => $point['appOptions']['toFloor']->building->name]
                        ];
                    }
                }
            }
        }
        $template->floorExchange = ['starting' => $starting, 'ending' => $ending];


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
        $form->addHidden('otherNode', NULL);

        $form->addButton('save','Uložit');
        $form->addButton('delete','Odstranit bod');

    }

    public function setBuildingsDictionary($buildings)
    {
        $this->buildings = $buildings;
    }
}
