<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CustomerLoginObserver implements ObserverInterface
{

    public function __construct(
        private readonly PublishingService $publishingService
    ) {
    }

    /**
     * @see @event customer_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        $eventIdentifier = $this->getEventIdentifier($customer);
        if ($eventIdentifier === null) {
            return;
        }
        $arguments = ['customerId' => $customer->getId()];

        $this->publishingService->publish(
            $eventIdentifier,
            $arguments
        );
    }

    private function getEventIdentifier(Customer $customer): ?string
    {
        return 'customer.login';
    }
}
