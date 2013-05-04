<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 2.5.13
 * Time: 10:38
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Floor;


use Doctrine\ORM\EntityManager;
use Maps\Model\Dao;
use Nette\Diagnostics\Debugger;
use Nette\Object;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TilesGeneratorCommand extends Command {

    /** @var \Maps\Model\Dao plan repository */
    private $repository;
    /** @var  TilesService */
    private $tileService;


    function __construct($repository, $tileService) {
        parent::__construct();
        $this->repository = $repository;
        $this->tileService = $tileService;
    }


    protected function configure() {
        $this->setName("tiles:generate")
                ->setDescription("Generates tiles for newly published plans revision.")->setDefinition(array(
                    new InputOption(
                        'force', NULL, InputOption::VALUE_NONE,
                        'Actually executes the generator.'
                    )
                ));

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($input->getOption('force') === TRUE) {
            $this->generateTiles($output);
        }
        else {
            $plans = $this->getPlansToPublish();
            if (empty($plans)) {
                $output->write("No plans are scheduled to tile generation.");
            }
            else {
                $output->writeln("Plans for following floors will be generated:" . PHP_EOL);
                /** @var $plan Plan */
                foreach ($plans as $plan) {
                    $output->write(Strings::toAscii("Building: " . $plan->getFloor()->getBuilding()->getName() .
                            ', floor ' . $plan->getFloor()->getReadableName() . ' - plan revision ' . $plan->getRevision()) . PHP_EOL);
                }
                $output->write(PHP_EOL . 'Use --force to execute the generator.');
            }
        }
    }


    public function generateTiles(OutputInterface $output) {
        set_time_limit(300);
        //load all plans in queue
        $plans = $this->getPlansToPublish();
        if (empty($plans)) {
            $output->writeln("No plans to generate.");
            return;
        }

        $output->writeln("STARTING TILES GENERATION FOR NEW PLAN REVISIONS:" . PHP_EOL);

        //each
        /** @var $plan Plan */
        foreach ($plans as $plan) {
            $floorName = "floor " . $plan->getFloor()->readableName . " of " . $plan->getFloor()->getBuilding()->name;
            $floorName = Strings::toAscii($floorName);

            $this->repository->getEntityManager()->beginTransaction();
            try {
                $output->writeln("New plan for " . $floorName . ":");
                //unset actualy active plan
                $output->write("- deactivating old revision");
                $q = new DeactivatePlansOfFloorQuery($plan->floor);
                $q->getQuery($this->repository)->execute();
                $output->writeln("... success");
                //set queued plan as active
                $plan->setPublished(TRUE);
                $plan->setPublishedDate(new \DateTime());
                $plan->setInPublishQueue(FALSE);
                //generate plan
                $output->write("- generating plan tiles");

                $this->tileService->generateTiles($plan);

                $output->writeln("... success");

                $output->write("- saving active plan");
                $this->repository->save(); //intentionally saves after every successful generation
                $this->repository->getEntityManager()->commit();
                $output->writeln("... success");
                $output->writeln("New plan for " . $floorName . " was published" . PHP_EOL);
            } catch (\Exception $e) {
                $this->repository->getEntityManager()->rollback();
                Debugger::log($e);
                $output->writeln(PHP_EOL . 'There was an unexpected exception during publishing - '.$e->getMessage().PHP_EOL.
                    'Check the logs dir for more information.' . PHP_EOL . 'Aborting.');
            }

        }
    }

    private function getPlansToPublish() {
        return $this->repository->findBy(['inPublishQueue' => TRUE, 'published' => FALSE]);
    }

}