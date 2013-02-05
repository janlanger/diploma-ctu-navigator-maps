<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Maps\Core;
/**
 * Description of PresenterLoader
 *
 * @author Honza
 */
class PresenterFactory extends \Nette\Application\PresenterFactory {
    private static $presenterNamespace="Maps\\Presenter";

    /**
     * Presenter class should be named by this pattern:
     * Maps\Presenter\Front/AdminModule\XXXXPresenter
     * @param string $presenter
     * @return string
     */
    public function formatPresenterClass($presenter) {
        if(\Nette\Utils\Strings::startsWith($presenter, "Nette:")) {
            return parent::formatPresenterClass($presenter);
        }
        $class=str_replace(':', 'Module\\', $presenter) . 'Presenter';
        //if(class_exists(self::$presenterNamespace."\\".$class)) {
            return self::$presenterNamespace."\\".$class;
        //}
        return $class;
    }

    public function unformatPresenterClass($class) {
        if(\Nette\Utils\Strings::startsWith($class, "Nette\\")) {
            return parent::unformatPresenterClass($class);
        }
        if(strpos($class, self::$presenterNamespace)!==FALSE) {
            $class=str_replace(self::$presenterNamespace."\\", "", $class); //removes namespace
            $class=str_replace("Module\\", ":", $class); //removes module suffix
        }

        return str_replace('\\', ':', substr($class, 0, -9)); //removes Presenter suffix

    }



}
