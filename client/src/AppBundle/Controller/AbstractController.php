<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\ReportSubmittedException;
use AppBundle\Exception\RestClientException;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\StepRedirector;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Router;

abstract class AbstractController extends Controller
{
    /**
     * @return RestClient
     */
    protected function getRestClient()
    {
        /** @var RestClient */
        $restClient = $this->get('rest_client');
        return $restClient;
    }

    /**
     * @param array $jmsGroups
     *
     * @return User
     */
    protected function getUserWithData(array $jmsGroups = [])
    {
        $jmsGroups[] = 'user';
        $jmsGroups = array_unique($jmsGroups);
        sort($jmsGroups);

        /** @var User */
        $user = $this->getUser();

        return $this->getRestClient()->get('user/' . $user->getId(), 'User', $jmsGroups);
    }

    /**
     * @return Client|null
     */
    protected function getFirstClient($groups = ['user', 'user-clients', 'client'])
    {
        $user = $this->getUserWithData($groups);

        $clients = $user->getClients();

        return (is_array($clients) && !empty($clients[0]) && $clients[0] instanceof Client) ? $clients[0] : null;
    }

    /**
     * @param Client $client
     * @param array  $groups
     *
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups = [])
    {
        $reports = $client->getReports();

        if (empty($reports)) {
            return [];
        }

        $ret = [];
        foreach ($reports as $report) {
            $id = $report->getId();
            $ret[$id] = $this->getReport($id, $groups);
        }

        return $ret;
    }

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @return Report
     */
    public function getReport($reportId, array $groups = [])
    {
        $groups[] = 'report';
        $groups[] = 'report-client';
        $groups[] = 'client';
        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching

        try {
            $report = $this->getRestClient()->get("report/{$reportId}", 'Report\\Report', $groups);
        } catch (RestClientException $e) {
            if ($e->getStatusCode() === 403 || $e->getStatusCode() === 404) {
                throw $this->createNotFoundException($e->getData()['message']);
            } else {
                throw $e;
            }
        }

        return $report;
    }

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @throws DisplayableException if report doesn't have specified section
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException if report is submitted
     *
     * @return Report
     */
    protected function getReportIfNotSubmitted($reportId, array $groups = [])
    {
        $report = $this->getReport($reportId, $groups);

        $sectionId = $this->getSectionId();
        if ($sectionId && !$report->hasSection($sectionId)) {
            throw new DisplayableException('Section not accessible with this report type.');
        }

        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    /**
     * @param int   $ndrId
     * @param array $groups
     *
     * @return Ndr
     */
    public function getNdr($ndrId, array $groups)
    {
        $groups[] = 'ndr';
        $groups[] = 'ndr-client';
        $groups[] = 'client';
        $groups = array_unique($groups);

        return $this->getRestClient()->get("ndr/{$ndrId}", 'Ndr\Ndr', $groups);
    }

    /**
     * @param int $reportId
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException if report is submitted
     *
     * @return Ndr
     *
     */
    protected function getNdrIfNotSubmitted($reportId, array $groups = [])
    {
        $report = $this->getNdr($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    /**
     * @return MailFactory
     */
    protected function getMailFactory()
    {
        /** @var MailFactory */
        $mailFactory = $this->get('AppBundle\Service\Mailer\MailFactory');
        return $mailFactory;
    }

    /**
     * @return MailSender
     */
    protected function getMailSender()
    {
        /** @var MailSender */
        $mailSender = $this->get('AppBundle\Service\Mailer\MailSender');
        return $mailSender;
    }

    /**
     * @return Router
     */
    private function getRouter()
    {
        /** @var Router */
        $router = $this->get('router');
        return $router;
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function routeExists(string $route)
    {
        return $this->getRouter()->getRouteCollection()->get($route) ? true : false;
    }

    /**
     * @return StepRedirector
     */
    protected function stepRedirector()
    {
        /** @var StepRedirector */
        $stepDirector = $this->get('step_redirector');
        return $stepDirector;
    }

    /**
     * Get referer, only if matching an existing route
     *
     * @param  Request $request
     * @param  array   $excludedRoutes
     * @return string|null  referer URL, null if not existing or inside the $excludedRoutes
     */
    protected function getRefererUrlSafe(Request $request, array $excludedRoutes = [])
    {
        $referer = $request->headers->get('referer');

        if (!is_string($referer)) return null;

        $refererUrlPath = parse_url($referer, \PHP_URL_PATH);

        if (!$refererUrlPath) return null;

        try {
            $routeParams = $this->getRouter()->match($refererUrlPath);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
        $routeName = $routeParams['_route'];
        if (in_array($routeName, $excludedRoutes)) {
            return null;
        }
        unset($routeParams['_route']);

        return $this->getRouter()->generate($routeName, $routeParams);
    }

    /**
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @param  Client     $client
     * @throws \Exception
     * @return string
     */
    protected function generateClientProfileLink(Client $client)
    {
        $client = $this->getRestClient()->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);

        $report = $client->getCurrentReport();

        if ($report instanceof Report) {
            // generate link
            return $this->generateUrl('report_overview', ['reportId' => $report->getId()]);
        }

        /** @var LoggerInterface */
        $logger = $this->get('logger');

        $logger->log(
            'warning',
            'Client entity missing current report when trying to generate client profile link'
        );

        throw new \Exception('Unable to generate client profile link.');
    }

    protected function getSectionId()
    {
        return null;
    }

    /**
     * @param string $description
     * @param int $statusCode
     * @return Response
     */
    protected function renderError(string $description, $statusCode = 500)
    {
        $text = $this->renderView('TwigBundle:Exception:template.html.twig', [
            'message' => 'Application error',
            'description' => $description
        ]);

        return new Response($text, $statusCode);
    }
}
