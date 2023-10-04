<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Model\ProcessedCustomerAddressesRegistry;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CustomerAddressSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publishingService,
        private readonly ProcessedCustomerAddressesRegistry $processedCustomerAddressesRegistry
    ) {
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
        $eventIdentifier = $this->getEventIdentifier($customerAddress);
        if ($eventIdentifier === null) {
            return;
        }
        $arguments = ['addressId' => $customerAddress->getId()];

        $this->publishingService->publish(
            $eventIdentifier,
            $arguments
        );

        $this->processedCustomerAddressesRegistry->setCustomerAddressProcessed($customerAddress);
    }

    private function getEventIdentifier(Address $customerAddress): ?string
    {
        if (!$customerAddress->getOrigData('entity_id')) {
            return 'customer.address.created';
        }
        if ($this->hasAddressChanged($customerAddress)) {
            return 'customer.address.updated';
        }
        return null;
    }

    private function hasAddressChanged(Address $customerAddress): bool
    {
        foreach ($customerAddress->getOrigData() as $key => $origValue) {
            if (in_array($key, ['updated_at'])) {
                continue;
            }
            if ($customerAddress->getData($key) != $origValue) {
                return true;
            }
        }
        return false;
    }
}
