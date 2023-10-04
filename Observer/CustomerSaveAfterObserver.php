<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use MageOS\CommonAsyncEvents\Model\ChangedCustomerDataRegistry;
use MageOS\CommonAsyncEvents\Model\ProcessedCustomersRegistry;

class CustomerSaveAfterObserver implements ObserverInterface
{

    public function __construct(
        private readonly Json $json,
        private readonly PublisherInterface $publisher,
        private readonly ProcessedCustomersRegistry $processedCustomersRegistry,
        private readonly ChangedCustomerDataRegistry $changedCustomerDataRegistry
    ) {
    }

    /**
     * @see @event customer_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        if ($this->processedCustomersRegistry->isCustomerProcessed($customer)) {
            return;
        }
        $eventIdentifier = $this->getEventIdentifier($customer);
        if ($eventIdentifier === null) {
            return;
        }
        $arguments = ['customerId' => $customer->getId()];
        $data = [$eventIdentifier, $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );

        $this->processedCustomersRegistry->setCustomerProcessed($customer);
    }

    private function getEventIdentifier(Customer $customer): ?string
    {
        if ($customer->getCreatedAt() === $customer->getUpdatedAt()) {
            return 'customer.created';
        }
        if ($this->changedCustomerDataRegistry->isCustomerDataChanged($customer)) {
            return 'customer.updated';
        }
        return null;
    }
}
