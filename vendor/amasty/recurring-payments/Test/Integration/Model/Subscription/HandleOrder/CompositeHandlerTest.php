<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Test\Integration\Model\Subscription\HandleOrder;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandler;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart\InvoiceHandlerPart;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart\OrderHandlerPart;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart\PaymentDataHandlerPart;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart\QuoteHandlerPart;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart\TransactionHandlerPart;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartsOrderedList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CompositeHandlerTest extends TestCase
{
    /**
     * @var CompositeHandler
     */
    private $model;

    /**
     * @var ResourceConnection
     */
    private $resource;

    protected function setUp(): void
    {
        $handlerParts = [
            'quote' => QuoteHandlerPart::class,
            'order' => OrderHandlerPart::class,
            'payment_data' => PaymentDataHandlerPart::class,
            'invoice' => InvoiceHandlerPart::class,
            'transaction' => TransactionHandlerPart::class,
        ];

        foreach ($handlerParts as $key => $className) {
            $handlerParts[$key] = Bootstrap::getObjectManager()->get($className);
        }
        $handlerPartsOrderedList = Bootstrap::getObjectManager()->create(HandlerPartsOrderedList::class, [
            'handlerParts' => $handlerParts
        ]);
        $this->model = Bootstrap::getObjectManager()->create(CompositeHandler::class, [
            'handlerPartsOrderedList' => $handlerPartsOrderedList,
        ]);
        $this->resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
    }

    /**
     * @magentoDataFixture Amasty_RecurringPayments::Test/Integration/_files/subscription.php
     */
    public function testHandle()
    {
        /** @var HandleOrderContext $context */
        $context = Bootstrap::getObjectManager()->create(HandleOrderContext::class);
        /** @var RepositoryInterface $subscriptionRepository */
        $subscriptionRepository = Bootstrap::getObjectManager()->create(RepositoryInterface::class);
        $subscription = $subscriptionRepository->getBySubscriptionId('subscription_test');

        $context->setSubscription($subscription);
        $context->setTransactionId('trans_id_test');

        $listForCount = [
            'order' => 'sales_order',
            'invoice' => 'sales_invoice',
            'transaction' => 'sales_payment_transaction',
        ];

        $selects = [];
        foreach ($listForCount as $key => $tableName) {
            $tableName = $this->resource->getTableName($tableName);
            $selects[$key] = $this->resource->getConnection()
                ->select()
                ->from($tableName, [new \Zend_Db_Expr('COUNT(*)')]);
        }

        $expectedCounts = [];
        foreach ($selects as $key => $select) {
            $expectedCounts[$key] = $this->resource->getConnection()->fetchOne($select);
            $expectedCounts[$key] += 1;
        }

        $this->model->handle($context);

        $actualCounts = [];
        foreach ($selects as $key => $select) {
            $actualCounts[$key] = $this->resource->getConnection()->fetchOne($select);
        }
        $this->assertEquals(
            $expectedCounts,
            $actualCounts
        );
    }
}
