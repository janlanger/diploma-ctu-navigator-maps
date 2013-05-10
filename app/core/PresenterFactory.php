<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Maps\Core;
/**
 * Description of PresenterLoader
 *
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class PresenterFactory extends \Nette\Application\PresenterFactory {
    private static $presenterNamespace = "Maps\\Presenter";

    /**
     * Presenter class should be named by this pattern:
     * Maps\Presenter\XXXXPresenter
     *
     * @param string $presenter
     * @return string
     */
    public function formatPresenterClass($presenter) {
        if (\Nette\Utils\Strings::startsWith($presenter, "Nette:")) {
            return parent::formatPresenterClass($presenter);
        }
        $class = str_replace(':', 'Module\\', $presenter) . 'Presenter';
        return self::$presenterNamespace . "\\" . $class;

    }

    /** {@inheritdoc} */
    public function unformatPresenterClass($class) {
        if (\Nette\Utils\Strings::startsWith($class, "Nette\\")) {
            return parent::unformatPresenterClass($class);
        }
        if (strpos($class, self::$presenterNamespace) !== FALSE) {
            $class = str_replace(self::$presenterNamespace . "\\", "", $class); //removes namespace
            $class = str_replace("Module\\", ":", $class); //removes module suffix
        }

        return str_replace('\\', ':', substr($class, 0, -9)); //removes Presenter suffix

    }


}
