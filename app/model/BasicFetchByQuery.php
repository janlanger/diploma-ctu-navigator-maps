<?php
namespace Maps\Model;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BasicFetchByQuery
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class BasicFetchByQuery extends Persistence\QueryObjectBase {
    
    private $conditions = [];
    
    function __construct($conditions) {
        $this->conditions = $conditions;
    }

    
    protected function doCreateQuery(Persistence\IQueryable $repository) {
        
        $where = [];
        $params = [];
        $i = 0;
        foreach($this->conditions as $condition=>$value) {
            $where[] = "b.".$condition.' = ?'.$i++;
            $params[] = $value;
        }
        $q = $repository->createQuery("SELECT b FROM ".$repository->getClassName()." b WHERE ".  implode(" AND ", $where))
                ->setParameters($params);
        return $q;
    }    //put your code here
}

?>
