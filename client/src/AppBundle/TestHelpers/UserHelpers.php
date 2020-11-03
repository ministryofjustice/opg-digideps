<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\User;
use Faker;
use Symfony\Component\Serializer\SerializerInterface;

class UserHelpers
{
    /** @var Serializer */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->faker = Faker\Factory::create();
    }

    /**
     * @param array|null $data
     * @return User|array|object
     */
    public function createUser(?array $data = null)
    {
        if (!empty($data)) {
            return $this->serializer->deserialize(json_encode($data), User::class, 'json');
        }

        return (new User())
            ->setId(1)
            ->setFirstname($this->faker->firstName)
            ->setLastname($this->faker->lastName)
            ->setRoleName($this->faker->jobTitle)
            ->setEmail($this->faker->safeEmail);
    }

    /**
     * @return User|array|object
     */
    public function createProfAdminUser(?int $id = null)
    {
        return $this->createUser(
            [
                'id' => $id ? $id : 1,
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'role_name' => User::ROLE_PROF_ADMIN,
                'email' => $this->faker->safeEmail,
            ]
        );
    }
}
