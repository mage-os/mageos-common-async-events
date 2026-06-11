<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Model\Address;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Save a list of already processed customer addresss during this request.
 */
class ProcessedCustomerAddressesRegistry
{
    private const FIELD_NAME_IDENTIFIER = 'address_id';

    /** @var array|string[] */
    private array $processedCustomerAddressIdentifiers = [];

    public function setCustomerAddressProcessed(Address $customerAddress): void
    {
        $this->processedCustomerAddressIdentifiers[$this->getIdentifier($customerAddress)] = true;
    }

    public function isCustomerProcessed(Address $customerAddress): bool
    {
        $identifier = $this->getIdentifier($customerAddress);

        if (isset($this->processedCustomerAddressIdentifiers[$identifier])) {
            return true;
        }
        return false;
    }

    private function getIdentifier(Address $customerAddress)
    {
        return $customerAddress->getData(self::FIELD_NAME_IDENTIFIER);
    }
}
