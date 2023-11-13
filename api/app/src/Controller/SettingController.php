<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setting")
 */
class SettingController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function getSetting(Request $request, $id)
    {
        $setting = $this->getRepository(EntityDir\Setting::class)->find($id); /* @var $setting EntityDir\Setting */

        $this->formatter->setJmsSerialiserGroups(['setting']);

        return $setting ?: [];
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function upsertSetting(Request $request, $id)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'content' => 'notEmpty',
            'enabled' => 'mustExist',
        ]);

        $setting = $this->getRepository(EntityDir\Setting::class)->find($id); /* @var $setting EntityDir\Setting */
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
