<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.2.13
 * Time: 12:31
 * To change this template use File | Settings | File Templates.
 */
abstract class Repository extends \Nette\Object {
    /** @var \Nette\Database\Connection */
    protected $connection;

    public function __construct(\Nette\Database\Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * Vrací objekt reprezentující databázovou tabulku.
     * @return \Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        // název tabulky odvodíme z názvu třídy
        preg_match('#(\w+)Repository$#', get_class($this), $m);
        return $this->connection->table(lcfirst($m[1]));
    }

    public function getGridDatasource() {
        return $this->getTable();
    }

    /**
     * Vrací všechny řádky z tabulky.
     * @return \Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vrací řádky podle filtru, např. array('name' => 'John').
     * @return \Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }


}
