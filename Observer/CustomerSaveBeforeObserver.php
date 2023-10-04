<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Model\ChangedCustomerDataRegistry;

class CustomerSaveBeforeObserver implements ObserverInterface
{
    public function __construct(
        private readonly ChangedCustomerDataRegistry $newCustomersRegistry
    ) {
    }

    /**
     * If the customer data is changed, store it in the changed customers registry.
     * This info is needed later in the customer_save_after observer
     * because we can't find out there directly if it's a changed customer.
     *
     * @see @event customer_save_before
     */
    public function execute(Observer $observer): void
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');
        $this->newCustomersRegistry->addCustomerIfChanged($customer);
    }
}
