<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Crawler\HttpClient;

use Amasty\Fpc\Model\Queue\Page;
use Amasty\Fpc\Model\QueuePageRepository;
use Amasty\Fpc\Model\ResourceModel\Queue\Page\Collection as PageCollection;
use GuzzleHttp\Exception\ClientException;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;

class Client implements CrawlerClientInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var QueuePageRepository
     */
    private $queuePageRepository;

    private $method = null;

    public function __construct(
        LoggerInterface $logger,
        \GuzzleHttp\Client $client,
        QueuePageRepository $queuePageRepository
    ) {
        $this->logger = $logger;
        $this->client = $client;
        $this->queuePageRepository = $queuePageRepository;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function execute(
        PageCollection $pageCollection,
        array $requestCombinations,
        \Closure $getRequestParams
    ): \Generator {
        /** @var Page $page */
        foreach ($pageCollection as $page) {
            foreach ($requestCombinations as $combination) {
                $response = null;
                $requestStartTime = microtime(true);

                try {
                    $response = $this->client->request(
                        $this->getMethod(),
                        $page->getUrl(),
                        $getRequestParams($page, $combination)
                    );
                } catch (ClientException $e) {
                    $response = $e->getResponse();
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                } finally {
                    $requestTime = microtime(true) - $requestStartTime;
                }

                if (!$response) {
                    continue;
                }

                yield new DataObject([
                    'page' => $page,
                    'status' => $response->getStatusCode(),
                    'load_time' => $requestTime,
                    'combination' => $combination,
                ]);
            }

            $this->queuePageRepository->delete($page);
        }
    }

    private function getMethod(): string
    {
        return $this->method ?? 'GET';
    }
}
