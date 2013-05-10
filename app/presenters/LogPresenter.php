<?php
namespace Maps\Presenter;


use DataGrid\DataGrid;
use Nette\Application\Responses\TextResponse;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;

/**
 * Class LogPresenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class LogPresenter extends SecuredPresenter {

    protected function beforeRender() {
        if ($this->getView() != 'default') {
            $this->addBreadcrumb('Log:', 'Log');
        }
        parent::beforeRender();
    }

    /**
     * @param string $file file to show
     * @throws \Nette\Application\BadRequestException
     */
    public function actionShowLogfile($file) {
        $dir = Debugger::$logDirectory;
        if (file_exists($dir . '/' . $file) && Strings::startsWith(realpath($dir . '/' . $file), $dir)) {
            $response = new TextResponse(file_get_contents($dir . '/' . $file));
            if (pathinfo($file, PATHINFO_EXTENSION) != 'html') {
                $this->getContext()->httpResponse->setContentType('text/plain', 'UTF-8');
            }

            $this->sendResponse($response);
        }
        else {
            throw new \Nette\Application\BadRequestException(404);
        }
    }

    public function createComponentFileGrid($name) {
        $grid = new DataGrid($this, $name);
        $items = array();
        foreach (\Nette\Utils\Finder::findFiles("*.*")->in(\Nette\Diagnostics\Debugger::$logDirectory) as $file) {
            $items[]['file'] = basename($file);
        }
        rsort($items);
        $datasource = new \DataGrid\DataSources\PHPArray\PHPArray($items);
        $grid->setDataSource($datasource);
        $grid->addColumn('file', 'Soubor');
        $grid->keyName = 'file';
        $grid->addActionColumn('action', 'Akce');
        $grid->addAction('Zobrazit', "showLogfile")
                ->getHtml()->addAttributes(array('target' => '_blank'));

    }

    public function createComponentApiLog($name) {
        $grid = new DataGrid($this, $name);
    }

}