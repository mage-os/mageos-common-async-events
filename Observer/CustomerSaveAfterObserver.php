<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use MageOS\CommonAsyncEvents\Model\ProcessedCustomersRegistry;

class CustomerSaveAfterObserver implements ObserverInterface
{
    private Json $json;
    private PublisherInterface $publisher;
    private ProcessedCustomersRegistry $processedCustomersRegistry;

    public function __construct(
        Json $json,
        PublisherInterface $publisher,
        NewCustomersRegistry $newCustomersRegistry,
        ProcessedCustomersRegistry $processedCustomersRegistry
    ) {
        $this->json = $json;
        $this->publisher = $publisher;
        $this->processedCustomersRegistry = $processedCustomersRegistry;
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
        $arguments = ['customerId' => $customer->getId()];
        $data = [$this->getEventIdentifier($customer), $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );

        $this->processedCustomersRegistry->setCustomerProcessed($customer);
    }

    private function getEventIdentifier(Customer $customer): string
    {
        if ($customer->getCreatedAt() === $customer->getUpdatedAt()) {
            return 'customer.created';
        }
        return 'customer.updated';
    }
}
