<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\RecurringStripe\Api\CustomerRepositoryInterface;
use Amasty\Stripe\Api\Data\CustomerInterface;
use Amasty\Stripe\Model\CustomerFactory;
use Amasty\Stripe\Model\ResourceModel\Customer;
use Amasty\Stripe\Model\ResourceModel\Customer\CollectionFactory;
use Amasty\Stripe\Model\Repository\CustomerRepository as StripeCustomerRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class CustomerRepository extends StripeCustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var Customer
     */
    private $customerResource;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CustomerFactory $customerFactory,
        Customer $customerResource,
        CollectionFactory $customerCollectionFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        parent::__construct(
            $searchResultsFactory,
            $customerFactory,
            $customerResource,
            $customerCollectionFactory,
            $criteriaBuilder
        );
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
    }

    /**
     * @inheritDoc
     */
    public function getByStripeId(string $stripeId): CustomerInterface
    {
        /** @var \Amasty\Stripe\Model\Customer $customer */
        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $stripeId, CustomerInterface::STRIPE_CUSTOMER_ID);
        if (!$customer->getEntityId()) {
            throw new NoSuchEntityException(__('Customer with specified ID "%1" not found.', $stripeId));
        }

        return $customer;
    }
}
