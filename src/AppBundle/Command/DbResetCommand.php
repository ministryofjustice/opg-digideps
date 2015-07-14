<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DbResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('digideps:db-reset');
        $this->setDescription('Drops all tables excluding oauth2 tables');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allTables = [];
        $classNames = [];
        $exclude = [ 'oauth2_client', 'access_token', 'refresh_token', 'auth_code', 'AuthCode', 'Client', 'AccessToken', 'RefreshToken' ];
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $allMetaData = $em->getMetadataFactory()->getAllMetadata();
        
        foreach($allMetaData as $classMetaData){
            if(!in_array($classMetaData->getTableName(), $exclude)){
                $allTables[] = $classMetaData->getTableName();
                $classNames[] = $classMetaData->getName();
            }
        }
        
        //$allTables[] = 'migrations';
        $allTables = array_unique($allTables);
        $classNames = array_unique($classNames);
        
        $command = $this->getApplication()->find('doctrine:schema:drop');
        
        $arguments = [ 'command' => 'doctrine:schema:drop', '--dump-sql' => true ];
        $input = new ArrayInput($arguments); 
        $returnCode = $command->run($input,$output);
        var_dump(get_class($command)); die('sfdsfds');
    }
}