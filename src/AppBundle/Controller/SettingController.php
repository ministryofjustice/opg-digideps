<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/setting")
 */
class SettingController extends RestController
{
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getSetting(Request $request, $id)
    {
        $setting = $this->findEntityBy(EntityDir\Setting::class, $id); /* @var $setting EntityDir\Setting */

        $this->setJmsSerialiserGroups(['setting']);

        return $setting;

    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function upsertSetting(Request $request, $id)
    {
        $data = $this->deserializeBodyContent($request, [
            'content' => 'notEmpty',
            'enabled' => 'mustExist',
        ]);

        $setting = $this->getRepository(EntityDir\Setting::class)->find($id); /* @var $setting EntityDir\Setting */
        if ($setting) { //update
            $setting->setContent($data['content']);
            $setting->setEnabled($data['enabled']);
        } else { //create new one
            $setting = new EntityDir\Setting($id, $data['content'], $data['enabled']);
            $this->getEntityManager()->persist($setting);
        }

        $this->getEntityManager()->flush($setting);

        return $setting->getId();
    }

    /**
     * Delete note.
     *
     * @Method({"DELETE"})
     * @Route("{id}")
     * @Security("has_role('ROLE_ORG')")
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        try {
            /** @var $note EntityDir\Note $note */
            $note = $this->findEntityBy(EntityDir\Note::class, $id);

            // enable if the check above is removed and the note is available for editing for the whole team
            $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

            $this->getEntityManager()->remove($note);

            $this->getEntityManager()->flush($note);
        } catch (\Exception $e) {
            $this->get('logger')->error('Failed to delete note ID: ' . $id . ' - ' . $e->getMessage());
        }

        return [];
    }
}
