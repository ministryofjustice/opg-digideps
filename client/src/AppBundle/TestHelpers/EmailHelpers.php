<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Model\Email;
use Faker\Factory;

class EmailHelpers
{
    /**
     * @return Email
     */
    public static function createEmail()
    {
        $faker = Factory::create();

        return (new Email())
            ->setFromEmailNotifyID($faker->uuid)
            ->setToEmail($faker->safeEmail)
            ->setFromName($faker->name)
            ->setSubject($faker->realText(35))
            ->setTemplate($faker->uuid)
            ->setParameters($faker->words(3));
    }
}
