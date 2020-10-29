<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\User;
use Faker;
use Symfony\Component\Serializer\Serializer;

class UserHelpers
{
    /** @var Serializer */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createUser(?array $data)
    {
        if (!empty($data)) {
            $this->serializer->deserialize($data, User::class, 'array');
        }

        $faker = Faker\Factory::create();

        return (new User())
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setRoleName($faker->jobTitle)
            ->setEmail($faker->safeEmail);
    }
}
