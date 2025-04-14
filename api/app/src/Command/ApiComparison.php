<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Service\ResponseComparison\ResponseComparer;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use Predis\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ApiComparison extends Command
{
    public static $defaultName = 'digideps:api:api-comparison';

    private EntityManagerInterface $entityManager;
    protected ClientInterface $httpClient;
    private readonly Client $redis;
    private string $baseurl;

    /** @var array<string, ResponseComparer> */
    private array $comparerMap = [];

    /**
     * @param iterable<ResponseComparer> $comparers
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ClientInterface $httpClient,
        Client $redis,
        iterable $comparers,
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->redis = $redis;

        foreach ($comparers as $comparer) {
            $this->comparerMap[$comparer->getRoute()] = $comparer;
        }

        $url = getenv('API_URL');
        if (!is_string($url) || empty($url)) {
            throw new \RuntimeException('Invalid or missing API_URL env variable');
        }
        $this->baseurl = $url;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('legacyRoute', InputArgument::REQUIRED, 'Legacy URI')
            ->addArgument('newRoute', InputArgument::REQUIRED, 'New URI');
    }

    protected function getIdsToUse(string $sqlStatement): array
    {
        try {
            $conn = $this->entityManager->getConnection();

            if (!$conn->isConnected()) {
                $conn->connect();
            }

            $result = $conn->executeQuery($sqlStatement);
            $arrayAssociative = $result->fetchAllAssociative();

            assert(is_array($arrayAssociative), 'Expected an array from fetchAllAssociative()');
            assert(count($arrayAssociative) > 0, 'Expected at least one row from the SQL query');

            foreach ($arrayAssociative as $row) {
                assert(is_array($row), 'Each result row should be an array');
                assert(array_key_exists('user_id', $row), 'Each row must contain a user_id field');
            }

            return $arrayAssociative;
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Database query failed: '.$e->getMessage(), 0, $e);
        } catch (\Throwable $t) {
            throw new \RuntimeException('Unexpected error during DB query: '.$t->getMessage(), 0, $t);
        }
    }

    protected function getAuthToken(int $user_id): string
    {
        $user = $this->entityManager->getRepository(User::class)->find($user_id);

        assert(null !== $user, "User with ID {$user_id} not found");
        assert($user instanceof User, "Expected instance of User for {$user_id}");

        try {
            $token = new UsernamePasswordToken($user, 'none', $user->getRoles());
            $authToken = 'frontend_'.$user->getId().'_'.sha1(microtime().spl_object_hash($user).rand(1, 999));
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error generating auth token for user_id {$user_id}: ".$e->getMessage(), 0, $e);
        }

        try {
            $this->redis->set($authToken, serialize($token));
        } catch (\Throwable $e) {
            throw new \RuntimeException("Error setting redis record for user {$user_id}: ".$e->getMessage(), 0, $e);
        }

        return $authToken;
    }

    protected function getUrls(string $baseUrl, string $legacyRoute, string $newRoute, array $ids): array
    {
        assert(
            1 === preg_match('#^https?://#', $baseUrl),
            sprintf('Base URL "%s" must start with http:// or https://', $baseUrl)
        );

        $legacyUrl = $this->injectIdsIntoRoute($legacyRoute, $ids);
        $newUrl = $this->injectIdsIntoRoute($newRoute, $ids);

        $fullLegacyUrl = rtrim($baseUrl, '/').'/'.ltrim($legacyUrl, '/');
        $fullNewUrl = rtrim($baseUrl, '/').'/'.ltrim($newUrl, '/');

        assert(
            !str_contains($fullLegacyUrl, $legacyUrl.'//'),
            sprintf('URL "%s" contains more than one slash after base URL', $fullLegacyUrl)
        );

        return [
            'legacyUrl' => $fullLegacyUrl,
            'newUrl' => $fullNewUrl,
        ];
    }

    private function injectIdsIntoRoute(string $route, array $ids): string
    {
        preg_match_all('/\{[^}]+\}/', $route, $matches);
        $placeholderCount = count($matches[0]);
        $idCount = count($ids);

        assert(
            $placeholderCount === $idCount,
            sprintf(
                'Mismatched ID count: expected %d IDs to match %d placeholders in route "%s"',
                $placeholderCount,
                $idCount,
                $route
            )
        );

        $result = preg_replace_callback('/\{[^}]+\}/', function () use (&$ids) {
            return (string) array_shift($ids);
        }, $route);

        assert(is_string($result), 'preg_replace_callback must return string');

        return $result;
    }

    private function extractNonUserIds(array $row): array
    {
        try {
            $extracted_ids = array_values(array_filter($row, fn ($key) => 'user_id' !== $key, ARRAY_FILTER_USE_KEY));
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error extracting ids from row: '.$e->getMessage(), 0, $e);
        }

        assert(count($extracted_ids) > 0, 'No ids other than user_id exist in row');

        return $extracted_ids;
    }

    protected function getApiResponse(string $authToken, string $url): ResponseInterface
    {
        try {
            return $this->httpClient->request('GET', $url, [
                'headers' => [
                    'AuthToken' => $authToken,
                    'ClientSecret' => getenv('SECRETS_FRONT_KEY'),
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('Failed to fetch API response from URL "%s": %s', $url, $e->getMessage()), 0, $e);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $failed = false;

        $legacyRoute = $input->getArgument('legacyRoute');
        if (!is_string($legacyRoute)) {
            throw new \InvalidArgumentException('Expected legacyRoute argument to be a string.');
        }

        $newRoute = $input->getArgument('newRoute');
        if (!is_string($newRoute)) {
            throw new \InvalidArgumentException('Expected newRoute argument to be a string.');
        }

        $comparer = $this->comparerMap[$legacyRoute] ?? throw new \InvalidArgumentException("No comparer defined for route: $legacyRoute");

        $rows = $this->getIdsToUse($comparer->getSqlStatement());

        foreach ($rows as $row) {
            try {
                $user_id = $row['user_id'];
                $authToken = $this->getAuthToken($user_id);
                $ids = $this->extractNonUserIds($row);
                $urls = $this->getUrls($this->baseurl, $legacyRoute, $newRoute, $ids);

                $resultLegacy = $this->getApiResponse($authToken, $urls['legacyUrl']);
                $resultNew = $this->getApiResponse($authToken, $urls['newUrl']);

                $isEqual = $comparer->compare($resultLegacy, $resultNew);

                if (!$isEqual) {
                    $output->writeln('<comment>Differences detected for user_id: </comment>'.$user_id);
                } else {
                    $output->writeln('<info>No differences found for user_id: </info>'.$user_id);
                }
            } catch (\Exception $e) {
                $output->writeln('<error>'.$e->getMessage().'</error>');
                $failed = true;
                continue;
            }
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
