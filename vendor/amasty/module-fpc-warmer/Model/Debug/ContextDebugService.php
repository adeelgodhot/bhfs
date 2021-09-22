<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Debug;

use Amasty\Fpc\Model\ResourceModel\DebugContext as DebugContextResource;
use Amasty\Fpc\Model\ResourceModel\DebugContext\CollectionFactory as DebugContextCollectionFactory;
use Magento\Framework\Serialize\Serializer;

class ContextDebugService
{
    /**
     * @var DebugContextCollectionFactory
     */
    private $debugCollectionFactory;

    /**
     * @var DebugContextResource
     */
    private $debugContextResource;

    /**
     * @var DebugContextFactory
     */
    private $debugContextFactory;

    /**
     * @var Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        DebugContextCollectionFactory $debugCollectionFactory,
        DebugContextResource $debugContextResource,
        DebugContextFactory $debugContextFactory,
        Serializer\Json $jsonSerializer
    ) {
        $this->debugCollectionFactory = $debugCollectionFactory;
        $this->debugContextResource = $debugContextResource;
        $this->debugContextFactory = $debugContextFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function flush()
    {
        $this->debugContextResource->flush();
    }

    public function addDebugLog(string $url, array $debugData)
    {
        /** @var DebugContext $debugContext */
        $debugContext = $this->debugContextFactory->create();
        $debugContext->setData([
            DebugContext::URL => $url,
            DebugContext::CONTEXT_DATA => $this->jsonSerializer->serialize($debugData),
        ]);
        $this->debugContextResource->save($debugContext);
    }

    public function getDebugList(string $url)
    {
        /** @var DebugContextResource\Collection $collection */
        $collection = $this->debugCollectionFactory->create();
        $collection->addFieldToFilter(DebugContext::URL, $url);

        return $collection->getItems();
    }
}
