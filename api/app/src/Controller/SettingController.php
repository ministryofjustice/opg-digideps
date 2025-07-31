<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Entity\Setting;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/setting')]
class SettingController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    public function getSetting(int $id): array|Setting
    {
        $setting = $this->em->getRepository(EntityDir\Setting::class)->find($id);

        $this->formatter->setJmsSerialiserGroups(['setting']);

        return $setting ?: [];
    }

    #[Route(path: '/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function upsertSetting(Request $request, string $id): string
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'content' => 'notEmpty',
            'enabled' => 'mustExist',
        ]);

        $setting = $this->em->getRepository(EntityDir\Setting::class)->find($id);
        if ($setting) { // update
            $setting->setContent($data['content']);
            $setting->setEnabled($data['enabled']);
        } else { // create new one
            $setting = new EntityDir\Setting($id, $data['content'], $data['enabled']);
            $this->em->persist($setting);
        }

        $this->em->flush($setting);

        return $setting->getId();
    }
}
