<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationCommand extends Command
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var string */
    private $rootDir;

    public function __construct(ValidatorInterface $validator, string $rootDir)
    {
        $this->validator = $validator;
        $this->rootDir = $rootDir;

        parent::__construct();
    }

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
        $data = $this->validator->getMetadataFor($entity); /* @var $data \Symfony\Component\Validator\Mapping\ClassMetadata */

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
        foreach (glob($this->rootDir . '/src/App/Entity/*.php') as $entity) {
            if (preg_match('/([A-Z][a-z]+)\.php$/', $entity, $matches)) {
                $className = '\\App\\Entity\\' . $matches[1];
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
