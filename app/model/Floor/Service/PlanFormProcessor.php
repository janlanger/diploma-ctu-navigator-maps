<?php
namespace Maps\Model\Floor\Service;
use Maps\Model\Floor\Plan;
use Maps\Model\Persistence\BaseFormProcessor;

/**
 * Process plan upload form submission
 *
 * @package Maps\Model\Floor\Service
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class PlanFormProcessor extends BaseFormProcessor {

    /** {@inheritdoc} */
    protected function setData($entity, $values) {
        if (isset($values['sourceFile'])) {
            $file = $this->handleUpload($values['sourceFile'], WWW_DIR . '/data/plans/raw', $this->getPlanFileName($entity, $values));
            if ($file == NULL) {
                unset($values['sourceFile']);
            }
            else {
                $values['sourceFile'] = $file;
            }
        }

        parent::setData($entity, $values);
    }

    /**
     * @param Plan $entity
     * @param array $values
     * @return string file name
     */
    private function getPlanFileName(Plan $entity, $values) {
        return \Nette\Utils\Strings::webalize($entity->floor->building->name . '-' . $entity->floor->floorNumber . '-' . $entity->floor->name);
    }


}

?>
