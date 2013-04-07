<?php
namespace Maps\Presenter;
use DataGrid\DataGrid;
use DataGrid\DataSources\Doctrine\QueryBuilder;
use Maps\Model\Metadata\Changeset;
use Maps\Model\Metadata\Queries\MyProposalsQuery;
use Nette\Application\BadRequestException;
use Nette\Security\AuthenticationException;
use Nette\Utils\Html;

/**
 * Dashboard presenter.
 */
class DashboardPresenter extends SecuredPresenter
{

    public function actionDefault() {
        if($this->getUser()->isInRole('admin')) {
            $this->redirect("Building:default");
            $this->terminate();
        }
    }

    public function createComponentMyProposals($name) {
        $q = new MyProposalsQuery($this->getUser()->id);
        $ds = new QueryBuilder($q->getQueryBuilder($this->getRepository('meta_changeset')));

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

    public function handleWithdraw($id) {
        $changeset = $this->getRepository('meta_changeset')->find($id);
        if($changeset == NULL) {
            throw new BadRequestException(404);
        }
        if($changeset->submittedBy->id != $this->getUser()->id) {
            throw new AuthenticationException();
        }
        $changeset->setState(Changeset::STATE_WITHDRAWN);
        $this->getRepository('meta_changeset')->save($changeset);
        $this->flashMessage('Návrh byl stažen', self::FLASH_SUCCESS);
        $this->redirect('this');
    }

}
