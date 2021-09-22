<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Indexer;

use Amasty\Label\Model\Indexer\LabelIndexerRegistry as IndexationStatusRegistry;
use Amasty\Label\Model\Label;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class LabelIndexer extends AbstractProcessor implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    const INDEXER_ID = 'amasty_label';

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var LabelIndexerRegistry
     */
    private $labelIndexerRegistry;

    public function __construct(
        IndexBuilder $indexBuilder,
        EventManager $eventManager,
        CacheInterface $cacheManager,
        IndexerRegistry $indexerRegistry,
        IndexationStatusRegistry $labelIndexerRegistry
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->eventManager = $eventManager;
        $this->cacheManager = $cacheManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->labelIndexerRegistry = $labelIndexerRegistry;
    }

    /**
     * Execute materialization on ids entities
     * @param int[] $ids
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->cacheManager->clean($this->getIdentities());
    }

    /**
     * Get affected cache tags
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [Label::CACHE_TAG];
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param array $ids
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws LocalizedException
     * @return void
     */
    public function executeRow($id)
    {
        if ($this->getIndexer()->isScheduled()) {
            return;
        }

        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }

        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID list. Template method
     * @param $ids
     * @throws LocalizedException
     */
    private function doExecuteList($ids)
    {
        $ids = (array) $ids;

        if ($this->labelIndexerRegistry->isCanExecuteList($ids)) {
            $this->indexBuilder->reindexByProductId($ids);
            $this->labelIndexerRegistry->registerIndexedProducts($ids);
        }
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @throws LocalizedException
     * @return void
     */
    private function doExecuteRow($id)
    {
        if ($this->labelIndexerRegistry->isCanExecuteProductId((int) $id)) {
            $this->indexBuilder->reindexByProductId($id);
            $this->labelIndexerRegistry->registerIndexedProducts((array) $id);
        }
    }

    /**
     * @throws LocalizedException
     */
    public function doExecuteFull()
    {
        $this->executeFull();
    }

    /**
     * Execute partial indexation by ID
     * @param int $id
     * @throws LocalizedException
     */
    public function executeByLabelId($id)
    {
        $this->indexBuilder->reindexByLabelId($id);
    }

    /**
     * Execute partial indexation by ID
     * @param array $ids
     * @throws LocalizedException
     */
    public function executeByLabelIds($ids)
    {
        $this->indexBuilder->reindexByLabelIds($ids);
    }

    public function invalidateIndex()
    {
        $labelIndexer = $this->getIndexer();

        if (!$labelIndexer->isScheduled()) {
            $labelIndexer->invalidate();
        }
    }
}
