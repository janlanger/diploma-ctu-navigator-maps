<?php
namespace Maps\Components\GoogleMaps;
use Maps\Components\Forms\Form;
use Maps\Model\Building\DictionaryQuery;
use Maps\Model\Metadata\FloorConnection;
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

        $starting = [];
        $ending = [];
        if (!empty($this->floorExchangePaths)) {


            /** @var $path FloorConnection */
            foreach ($this->floorExchangePaths as $path) {


                foreach ($this->points as $id => $point) {
                    $node = NULL;
                    if ($path->nodeOne->id == $point['appOptions']['propertyId']) {
                        dump($path);
                        $starting[$path->nodeOne->id][] = [
                            'destinationNode' => $path->nodeTwo->id,
                            'pathId' => $path->id,
                            'destinationFloor' => ['id' => $path->getRevisionTwo()->getFloor()->id, 'name' => $path->getRevisionTwo()->getFloor()->readableName],
                            'destinationBuilding' => ['id' => $path->getRevisionTwo()->getFloor()->building->id, 'name' => $path->getRevisionTwo()->getFloor()->building->name]
                        ];
                    }
                    if ($path->nodeTwo->id == $point['appOptions']['propertyId']) {
                        $ending[$path->nodeTwo->id][] = [
                            'destinationNode' => $path->nodeOne->id,
                            'pathId' => $path->id,
                            'destinationFloor' => ['id' => $path->getRevisionOne()->getFloor()->id, 'name' => $path->getRevisionOne()->getFloor()->readableName],
                            'destinationBuilding' => ['id' => $path->getRevisionOne()->getFloor()->building->id, 'name' => $path->getRevisionOne()->getFloor()->building->name]
                        ];
                    }
                    unset($this->points[$id]['appOptions']['toFloor']);
                }
            }
        }

        $template = $this->createTemplate();
        $template->floorExchange = ['starting' => $starting, 'ending' => $ending];

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
        $form->addHidden('otherNode', NULL);

        $form->addButton('save','Uložit');
        $form->addButton('delete','Odstranit bod');

    }

    public function setBuildingsDictionary($buildings)
    {
        $this->buildings = $buildings;
    }
}
