<?php

namespace AppBundle\Controller\Odr\Finance;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class BankAccountsController extends AbstractController
{
    private static $odrJmsGroups = ['odr', 'client', 'odr-account', 'client-cot'];

    /**
     * @Route("/odr/{odrId}/finance/banks", name="odr-bank-accounts")
     *
     * @param int $odrId
     * @Template()
     *
     * @return array
     */
    public function indexAction($odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

//        echo "<pre>";\Doctrine\Common\Util\Debug::dump($odr, 4);die;

        return [
            'odr' => $odr,
            'subsection' => 'banks',
        ];
    }

    /**
     * @Route("/odr/{odrId}/finance/banks/upsert/{id}", name="odr_upsert_bank_account", defaults={ "id" = null })
     *
     * @param Request $request
     * @param int     $odrId
     * @param int     $id      account Id
     *
     * @Template()
     *
     * @return array
     */
    public function upsertAction(Request $request, $odrId, $id = null)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        $type = $id ? 'edit' : 'add';

        if ($type === 'edit') {
            if (!$odr->hasBankAccountWithId($id)) {
                throw new \RuntimeException('Bank Account not found.');
            }
            $bankAccount = $account = $this->getRestClient()->get('odr/account/'.$id, 'Odr\BankAccount');
            // not existingAccount.accountNumber or (existingAccount.requiresBankNameAndSortCode and not existingAccount.sortCode)
        } else { //add
            $bankAccount = new EntityDir\Odr\BankAccount();
            $bankAccount->setOdr($odr);
        }
        // display the checkbox if either told by the URL, or closing balance is zero, or it was previously ticked
        $form = $this->createForm(new FormDir\Odr\BankAccountType(), $bankAccount);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setOdr($odr);
            // if closing balance is set to non-zero values, un-close the account
            if ($type === 'edit') {
                $this->getRestClient()->put('/odr/account/'.$id, $bankAccount, ['bank-account']);
            } else {
                $this->getRestClient()->post('odr/'.$odrId.'/account', $bankAccount, ['bank-account']);
            }

            return $this->redirect($this->generateUrl('odr-bank-accounts', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'bankAccountId' => $id,
            'subsection' => 'banks',
            'form' => $form->createView(),
            'type' => $type,
        ];
    }

    /**
     * @Route("/odr/{odrId}/finance/banks/{id}/delete", name="odr_delete_bank_account")
     *
     * @param int $odrId
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($odrId, $id)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);

        if ($odr->hasBankAccountWithId($id)) {
            $this->getRestClient()->delete("/odr/account/{$id}");
        }

        return $this->redirect($this->generateUrl('odr-bank-accounts', ['odrId' => $odrId]));
    }
}
