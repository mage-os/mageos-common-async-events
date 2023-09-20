<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use MageOS\CommonAsyncEvents\Model\NewCustomersRegistry;
use MageOS\CommonAsyncEvents\Model\ProcessedCustomerAddressesRegistry;

class CustomerAddressSaveAfterObserver implements ObserverInterface
{
    private Json $json;
    private PublisherInterface $publisher;
    private NewCustomersRegistry $newCustomersRegistry;
    private ProcessedCustomerAddressesRegistry $processedCustomerAddressesRegistry;

    public function __construct(
        Json $json,
        PublisherInterface $publisher,
        NewCustomersRegistry $newCustomersRegistry,
        ProcessedCustomerAddressesRegistry $processedCustomerAddressesRegistry
    ) {
        $this->json = $json;
        $this->publisher = $publisher;
        $this->newCustomersRegistry = $newCustomersRegistry;
        $this->processedCustomerAddressesRegistry = $processedCustomerAddressesRegistry;
    }

    /**
     * @see @event customer_address_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        if ($this->processedCustomerAddressesRegistry->isCustomerProcessed($customerAddress)) {
            return;
        }
        $arguments = ['addressId' => $customerAddress->getId()];
        $data = [$this->getEventIdentifier($customerAddress), $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );

        $this->processedCustomerAddressesRegistry->setCustomerAddressProcessed($customerAddress);
    }

    private function getEventIdentifier(Address $customerAddress): string
    {
        if (!$customerAddress->getOrigData('address_id')) {
            return 'customer.address.created';
        }
        return 'customer.address.updated';
    }
}
