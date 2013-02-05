<?php

namespace Maps\Core;

use Nette\Environment;
/**
 * Description of ServiceFactories
 *
 * @author Jan -Quinix- Langer
 */
class ServiceFactories {

    /**
     *
     * @param type $appDir
     * @return PresenterFactory
     */
    public static function createPresenterFactory($appDir) {
        return new PresenterFactory($appDir, Environment::getContext());
    }

}
