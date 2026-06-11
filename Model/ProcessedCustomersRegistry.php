<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Save a list of already processed customers during this request.
 */
class ProcessedCustomersRegistry
{
    private const FIELD_NAME_IDENTIFIER = 'email';

    /** @var array|string[] */
    private array $processedCustomerIdentifiers = [];

    public function setCustomerProcessed(Customer $customer): void
    {
        $this->processedCustomerIdentifiers[$this->getIdentifier($customer)] = true;
    }

    public function isCustomerProcessed(Customer $customer): bool
    {
        $identifier = $this->getIdentifier($customer);

        if (isset($this->processedCustomerIdentifiers[$identifier])) {
            return true;
        }
        return false;
    }

    private function getIdentifier(Customer $customer)
    {
        return $customer->getData(self::FIELD_NAME_IDENTIFIER);
    }
}
