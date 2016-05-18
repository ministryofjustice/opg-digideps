<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\CourtOrderType;

/**
 * @codeCoverageIgnore
 */
class FixturesCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fixtures')
            ->setDescription('Add data from fixtures')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $em->clear();

        $em->beginTransaction();

        $this->cot($output);
        
        $fixtures = (array) $this->getContainer()->getParameter('fixtures');
        foreach ($fixtures as $email => $data) {
            $this->addSingleUser($output, ['email' => $email] + $data, ['flush' => false]);
        }
        $em->commit();
        $em->flush();
    }
    
    protected function cot(OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        $cotRepo = $em->getRepository('AppBundle\Entity\CourtOrderType');
        foreach(CourtOrderType::$fixtures as $id=>$name) {
            $output->write("COT $id ($name): ");
            if ($cotRepo->find($id)) {
                $output->writeln("skip");
            } else {
                $cot = new CourtOrderType();
                $cot
                    ->setId($id)
                    ->setName($name);
                $em->persist($cot);
                $output->writeln("added");
            }
        }
        $em->flush();
        
        
        
    }
    
    protected function addTransactionCategories()
    {
        
    }
}
