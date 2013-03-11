<?php
namespace Maps\Model\Floor;
use Maps\Model\Persistence\BaseFormProcessor;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlanFormProcessor
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class PlanFormProcessor extends BaseFormProcessor {


    protected function setData($entity, $values) {
        if (isset($values['sourceFile'])) {
            $file = $this->handleUpload($values['sourceFile'], WWW_DIR . '/data/plans/raw', $this->getPlanFileName($entity, $values));
            if ($file == null) {
                unset($values['sourceFile']);
            }
            else {
                $values['sourceFile'] = $file;
            }
        }

        parent::setData($entity, $values);
    }

    private function getPlanFileName(Plan $entity, $values) {
        return \Nette\Utils\Strings::webalize($entity->floor->building->name . '-' . $entity->floor->floorNumber . '-' . $entity->floor->name);
    }


}

?>