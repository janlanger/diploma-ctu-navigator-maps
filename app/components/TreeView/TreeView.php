<?php
namespace SeriesCMS\Components\TreeView;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TreeView
 *
 * @author Honza
 */
class TreeView extends \Nette\Application\UI\Control {
    
    private $actionColumn;
   // private $recursiveColumn;
    private $titleColumn;
    
    private $parentColumn;
    
    private $qb;
    
    private $actions = array();
    
    public function getActionColumn() {
        return $this->actionColumn;
    }

    public function setActionColumn($actionColumn) {
        $this->actionColumn = $actionColumn;
    }

    public function getTitleColumn() {
        return $this->titleColumn;
    }

    public function setTitleColumn($titleColumn) {
        $this->titleColumn = $titleColumn;
    }
    
    public function addAction($title, $action) {
        $this->actions[] = array('title'=>$title, 'action'=>$action);
    }
    
    public function setDatasource(QueryBuilder $qb) {
        $this->qb = $qb;
        $this->qb->parentColumn = $this->parentColumn;
        $this->qb->idColumn = $this->actionColumn;
    }
    
    public function render() {
        $this->template->registerHelperLoader("SeriesCMS\\Templates\\Templatehelpers::loader");
        $this->template->setFile(__DIR__.'/tree.latte');
        $this->template->data = $this->qb->fetch();
        $this->template->id = $this->actionColumn;
        $this->template->actions = $this->actions;
        $this->template->title = $this->titleColumn;
        $this->template->render();
    }

    public function getParentColumn() {
        return $this->parentColumn;
    }

    public function setParentColumn($parentColumn) {
        $this->parentColumn = $parentColumn;
    }



}

?>
