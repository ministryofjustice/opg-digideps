<?php declare(strict_types=1);


namespace App\Controller;

use App\Entity\UserResearch\UserResearchResponse;
use App\Repository\SatisfactionRepository;
use App\Repository\UserResearchResponseRepository;
use App\Factory\UserResearchResponseFactory;
use App\Service\Formatter\RestFormatter;
use DateTime;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class UserResearchController extends RestController
{
    private UserResearchResponseFactory $factory;
    private UserResearchResponseRepository $userResearchResponseRepository;
    private SatisfactionRepository $satisfactionRepository;
    private RestFormatter $formatter;

    public function __construct(
        UserResearchResponseFactory $factory,
        UserResearchResponseRepository $userResearchResponseRepository,
        SatisfactionRepository $satisfactionRepository,
        RestFormatter $formatter
    ) {
        $this->factory = $factory;
        $this->userResearchResponseRepository = $userResearchResponseRepository;
        $this->satisfactionRepository = $satisfactionRepository;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/user-research", name="create_user_research", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY') or has_role('ROLE_ORG')")
     */
    public function create(Request $request)
    {
        try {
            $formData = json_decode($request->getContent(), true);

            $formData['satisfaction'] = $this->satisfactionRepository->find($formData['satisfaction']);
            $userResearchResponse = $this->factory->generateFromFormData($formData);
            $this->userResearchResponseRepository->create($userResearchResponse, $this->getUser());

            $groups = $request->get('groups') ? $request->get('groups') : ['satisfaction', 'user-research', 'user'];
            $this->formatter->setJmsSerialiserGroups($groups);
            return 'Created';
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('UserResearchResponse not created: %s', $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/user-research", name="get_user_research", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getAll(Request $request)
    {
        try {
            $fromDateString = $request->get('fromDate', '');
            $fromDate = empty($fromDateString) ?
                (new DateTime('-5 years'))->setTime(0, 0, 1) : (new DateTime($fromDateString))->setTime(0, 0, 1);

            $toDateString = $request->get('toDate', '');
            $toDate = empty($toDateString) ?
                (new DateTime())->setTime(23, 59, 59) : (new DateTime($toDateString))->setTime(23, 59, 59);

            $groups = $request->get('groups') ? $request->get('groups') : ['satisfaction', 'user-research', 'user'];
            $this->formatter->setJmsSerialiserGroups($groups);

            return $this->userResearchResponseRepository->getAllFilteredByDate($fromDate, $toDate);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('There was a problem getting user research responses: %s', $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }
}
