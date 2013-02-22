<?php
namespace Maps\Model\FloorPlan;
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
        $file = $this->handleUpload($values['floorPlan'], WWW_DIR.'/data/plans', $this->getPlanFileName($entity, $values));
        if($file == null) {
            unset($values['floorPlan']);
        } else {
            $values['floorPlan'] = $file;
        }
        $values['building'] = $this->getEntityRepository('Maps\Model\Building\Building')->find($values['building']);
        parent::setData($entity, $values);
    }
    
    private function getPlanFileName(FloorPlan $entity, $values) {
        return \Nette\Utils\Strings::webalize($values['building'].'-'.$values['floorNumber'].'-'.$values['name']);                
    }

    
}

?>
