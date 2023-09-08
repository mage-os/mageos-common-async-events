<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Save a list of new customers.
 */
class NewCustomersRegistry
{
    const FIELD_NAME_IDENTIFIER = 'email';

    /** @var array|string[] */
    private array $newCustomerIdentifiers = [];

    public function addNewCustomer(Customer $customer): void
    {
        $this->newCustomerIdentifiers[$this->getIdentifier($customer)] = true;
    }

    public function isCustomerNew(Customer $customer): bool
    {
        $identifier = $this->getIdentifier($customer);

        if (isset($this->newCustomerIdentifiers[$identifier])) {
            unset ($this->newCustomerIdentifiers[$identifier]);
            return true;
        }
        return false;
    }

    private function getIdentifier(Customer $customer)
    {
        return $customer->getData(self::FIELD_NAME_IDENTIFIER);
    }
}
