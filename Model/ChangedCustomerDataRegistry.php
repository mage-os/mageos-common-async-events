<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Save customer data if it has changed
 */
class ChangedCustomerDataRegistry
{
    const FIELD_NAME_IDENTIFIER = 'email';

    /** @var array|string[] */
    private array $changedCustomerIdentifiers = [];

    public function addCustomerIfChanged(Customer $customer): void
    {
        if ($this->hasChanges($customer)) {
            $this->changedCustomerIdentifiers[$this->getIdentifier($customer)] = true;
        }
    }

    public function isCustomerDataChanged(Customer $customer): bool
    {
        $identifier = $this->getIdentifier($customer);

        if (isset($this->changedCustomerIdentifiers[$identifier])) {
            unset ($this->changedCustomerIdentifiers[$identifier]);
            return true;
        }
        return false;
    }

    private function getIdentifier(Customer $customer)
    {
        return $customer->getData(self::FIELD_NAME_IDENTIFIER);
    }

    private function hasChanges(Customer $customer): bool
    {
        foreach ($this->getCustomerFieldsToCompare() as $fieldname) {
            if ($customer->getOrigData($fieldname) != $customer->getData($fieldname)) {
                return true;
            }
        }
        return false;
    }

    /**
     * List of customer fields which are taken into account when we want to decide whether to
     * send a "customer.changed" event or not
     * It's public in order to allow overwriting it with an "after" plugin.
     *
     * @return string[]
     */
    public function getCustomerFieldsToCompare(): array
    {
        return [
            'email',
            'firstname',
            'lastname',
            'middlename',
            'prefix',
            'gender',
            'suffix',
            'dob',
            'group_id',
            'taxvat',
        ];
    }
}
