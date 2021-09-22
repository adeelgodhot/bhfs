<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Controller\ContextData;

use Amasty\Fpc\Model\Debug\ContextDebugService;
use Magento\Framework\App;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer;

class Index extends \Magento\Framework\App\Action\Action implements App\PageCache\NotCacheableInterface
{
    /**
     * @var App\Http\Context
     */
    private $httpContext;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var ContextDebugService
     */
    private $contextDebugService;

    /**
     * @var Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        App\Http\Context $httpContext,
        JsonFactory $jsonResultFactory,
        ContextDebugService $contextDebugService,
        Serializer\Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->contextDebugService = $contextDebugService;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute()
    {
        $url = rtrim($this->getRequest()->getParam('debug_url', ''), '/');
        $result = $this->jsonResultFactory->create();
        $vary = $this->httpContext->getVaryString(); // Force run plugin chain over getVaryString method
        $contextDefaultData = $this->httpContext->toArray()['default'];
        ksort($contextDefaultData);

        return $result->setData([
            'current_context' => [
                'context' => ['vary' => $vary] + $this->httpContext->getData(),
                'defaults' => $contextDefaultData,
            ],
            'page_context_data' => array_map(function ($debugData) {
                return $this->jsonSerializer->unserialize($debugData->getContextDataJson());
            }, $this->contextDebugService->getDebugList($url))
        ]);
    }
}
