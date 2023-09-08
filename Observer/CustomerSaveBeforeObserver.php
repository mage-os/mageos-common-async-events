<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Model\NewCustomersRegistry;

class CustomerSaveBeforeObserver implements ObserverInterface
{
    private NewCustomersRegistry $newCustomersRegistry;

    public function __construct(
        NewCustomersRegistry $newCustomersRegistry
    ) {
        $this->newCustomersRegistry = $newCustomersRegistry;
    }

    /**
     * If the customer is new, store it in the new customers registry.
     * This info is needed later in the customer_save_after observer
     * because we can't find out there directly if it's a new customer.
     *
     * @see @event customer_save_before
     */
    public function execute(Observer $observer): void
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');
        if (!$customer->getId()) {
            $this->newCustomersRegistry->addNewCustomer($customer);
        }
    }
}
