<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidationCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:validation')
            ->setDescription('Check or print validation rules')
            ->addOption('print', null, InputOption::VALUE_NONE, 'print validation rules')
        ;
    }

    private function getClassValidationRules($entity)
    {
        $validator = $this->getContainer()->get('validator');
        $data = $validator->getMetadataFor($entity); /* @var $data \Symfony\Component\Validator\Mapping\ClassMetadata */

        $ret = [];
        foreach ($data->getConstrainedProperties() as $property) {
            $propMetaData = $data->getMemberMetadatas($property)[0]; /* @var $propMetaData \Symfony\Component\Validator\Mapping\PropertyMetadata */
            $ret[$property] = $propMetaData->getConstraints();
        }

        return $ret;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ret = [];
        foreach (glob($this->getContainer()->get('kernel')->getRootDir() . '/../src/AppBundle/Entity/*.php') as $entity) {
            if (preg_match('/([A-Z][a-z]+)\.php$/', $entity, $matches)) {
                $className = '\\AppBundle\\Entity\\' . $matches[1];
                if (class_exists($className)) {
                    $ret[$className] = $this->getClassValidationRules(new $className());
                }
            }
        }

        if ($input->getOption('print')) {
            $output->writeln(print_r($ret, true));
        }
    }
}
