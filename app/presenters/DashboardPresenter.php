<?php
namespace Maps\Presenter;
use DataGrid\DataGrid;
use DataGrid\DataSources\Doctrine\QueryBuilder;
use DependentSelectBox\JsonDependentSelectBox;
use Maps\Components\Forms\Form;
use Maps\Model\Building\Queries\DictionaryQuery;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\Queries\MyProposalsQuery;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException;
use Nette\Utils\Html;

/**
 * Class DashboardPresenter
 *
 * @package Maps\Presenter
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class DashboardPresenter extends SecuredPresenter
{
    /** {@inheritdoc} */
    protected function beforeRender() {
        parent::beforeRender();
        JsonDependentSelectBox::tryJsonResponse($this);
    }


    public function actionDefault() {
        if($this->getUser()->isInRole('admin')) {
            $this->redirect("Building:default");
            $this->terminate();
        }
    }

    public function createComponentMyProposals($name) {
        $q = new MyProposalsQuery($this->getUser()->id);
        $ds = new QueryBuilder($q->getQueryBuilder($this->context->changesetRepository));

        $ds->setMapping([
            'id' => 'c.id',
            'date' => 'c.submitted_date',
            'state' => 'c.state',
            'comment' => 'c.comment',
            'processed_by' => 'u2_name',
            "processed_date" => "c.processed_date",
            'in_revision' => "r2_revision",
            'admin_comment' => 'c.admin_comment',
            'building' => 'b.name',
            'floor' => 'f.name',
        ]);

        $grid = new DataGrid($this, $name);

        $grid->setDataSource($ds);

        $states = [Changeset::STATE_NEW => 'Nový', Changeset::STATE_APPROVED => 'Přijatý', Changeset::STATE_REJECTED => 'Odmítnutý', Changeset::STATE_WITHDRAWN => 'Zrušený'];

        $grid->addDateColumn("date", 'Odesláno dne', "%d.%m.%Y %H:%M")->addDefaultSorting('desc');

        $grid->addColumn("state", "Stav návrhu")
                ->addSelectboxFilter($states, TRUE, FALSE);
        //$grid['state']->addDefaultFiltering(Changeset::STATE_NEW);
        $grid['state']->formatCallback[] = function ($value, $data) use ($states) {

            $el = Html::el('span')->setText($states[$value]);
            $el->class[] = 'label';
            switch ($value) {
                case Changeset::STATE_NEW:
                    $el->class[] = "label-info";
                    break;
                case Changeset::STATE_APPROVED:
                    $el->class[] = 'label-success';
                    break;
                case Changeset::STATE_REJECTED:
                    $el->class[] = 'label-important';
                    break;
            }
            return $el;
        };

        $grid->addColumn('building', 'Pro')->formatCallback[] = function($value, $data) {
            return $value.' ('.($data['floor']).')';
        };
        $grid->addColumn("processed_by", "Zpracoval");
        $grid->addColumn("in_revision", "V revizi");

        $grid['processed_by']->formatCallback[] = function ($value, $data) {
            if ($value != "") {
                return $value . " (" . $data["processed_date"]->format("d.m. H:i") . ")";
            }
        };

        $grid->addColumn("comment", 'Komentáře', 0);
        $grid['comment']->formatCallback[] = function ($value, $data) {
            $r = "";
            if ($value != "") {
                $r .= (string)Html::el("span class=badge")->setText("Uživatel")->setTitle($value);
            }
            if ($data['admin_comment'] != "") {
                $r .= ((string)Html::el("span class='badge badge-info'")->setText("Admin")->setTitle($data['admin_comment']));
            }

            return $r;

        };

        $grid->keyName = 'id';
        $grid->addActionColumn('Akce');
        $grid->addAction("Stáhnout návrh", "withdraw!")->ifDisableCallback = function($data) {
            return $data['state'] != Changeset::STATE_NEW;
        };
    }

    /**
     * @param $id changeset ID
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Security\AuthenticationException
     */
    public function handleWithdraw($id) {
        $changeset = $this->context->changesetRepository->find($id);
        if($changeset == NULL) {
            throw new BadRequestException(404);
        }
        if($changeset->submittedBy->id != $this->getUser()->id) {
            throw new AuthenticationException();
        }
        $changeset->setState(Changeset::STATE_WITHDRAWN);
        $this->context->changesetRepository->save($changeset);
        $this->flashMessage('Návrh byl stažen', self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    public function actionCreate() {
        if($this->getHttpRequest()->isAjax()) {
            $this->invalidateControl('form');
        }
    }

    public function createComponentForm($name) {
        $form = new Form($this, $name);

        $form->addSelect('buildings', 'Budova', $this->context->buildingRepository->fetchPairs(new DictionaryQuery(), 'id', 'name'))
            ->setPrompt("-- vyberte budovu --");
        $s = $form->addDependedSelect('floor', 'Podlaží', $form['buildings'], callback($this, 'getFloorDict'), FALSE)
            ->setPrompt("-- vyberte podlaží --");

        $form->addSubmit('ok', 'Odeslat')->onClick[] = function(SubmitButton $button) {
            $form = $button->getForm();
            if($form->isValid()) {
                $v = $form->getValues();
                $this->redirect("Metadata:proposal", ["floor"=>$v['floor']]);
                $this->terminate();
            }
        };;

    }

    /**
     * @param Form $form
     * @return array
     */
    public function getFloorDict(Form $form) {
        $values = $form->getValues();
        $r = [];
        if($values['buildings'] > 0) {
            $r = $this->context->floorRepository->fetchPairs(new \Maps\Model\Floor\Queries\DictionaryQuery($values['buildings']), 'id', 'name');
            if($r == NULL) {
                $r = array();
            }
        }
        return $r;
    }

}
