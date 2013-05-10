<?php
namespace Maps\Components\GoogleMaps;
use Maps\Components\Forms\Form;
use Maps\Model\Building\Building;
use Maps\Model\Building\Queries\DictionaryQuery;
use Maps\Model\Metadata\FloorConnection;
use Maps\Presenter\BasePresenter;
use Nette\Forms\IControl;

/**
 * Metadata editor component
 *
 * @author Jan Langer <langeja1@fit.cvut.cz
 * @package Maps\Components\GoogleMaps
 */
class PolyLinesEditor extends BaseMapControl {

    /** @var  IControl text field for definition */
    private $formField;
    /** @var  IControl submit element to handle */
    private $submit;
    /** @var  string used room prefix in this building */
    private $roomPrefix;

    /** @var  bool has this component used by another internally? */
    private $overridden;

    /** @var FloorConnection[] */
    private $floorExchangePaths;
    /** @deprecated @var array  */
    private $buildings=[];


    /**
     * @param FloorConnection[] $floorExchangePaths
     */
    public function setFloorExchangePaths($floorExchangePaths) {
        $this->floorExchangePaths = $floorExchangePaths;
    }

    /**
     * @return \Maps\Model\Metadata\FloorConnection[]
     */
    public function getFloorExchangePaths() {
        return $this->floorExchangePaths;
    }

    /**
     * @param IControl $control text field to read from
     */
    public function bindFormField(\Nette\Forms\IControl $control) {
        $this->formField = $control;
    }

    /**
     * True only generates map configuration to template, but doesn't initiate the map itself.
     * Used when metadata editor is embedded to other component
     * @param bool $overiden
     */
    public function setOverridden($overiden) {
        $this->overridden = $overiden;
    }

    /**
     * @return bool
     */
    public function getOverridden() {
        return $this->overridden;
    }

    /**
     * @param IControl $control submit to bind to
     */
    public function setSubmitButton(\Nette\Forms\IControl $control) {
        $this->submit = $control;
    }

    /**
     * @param string $roomPrefix room prefix used in this building
     */
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
        $template->overiden = $this->overridden;




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

    /**
     * @deprecated
     * @param Building[] $buildings
     */
    public function setBuildingsDictionary($buildings)
    {
        $this->buildings = $buildings;
    }
}
