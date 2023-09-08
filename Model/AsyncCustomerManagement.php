<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AsyncCustomerManagement
{
    private CustomerRepositoryInterface $customerRepository;
    private CustomerRegistry $customerRegistry;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * The CustomerRepository uses a CustomerRegistry which will cache entities on load. If the updates happen in a
     * different thread, there is a possibility that stale data is returned.
     * However, we still want to use the repository instead of using the resource model to preserve modifications
     * added by plugins.
     *
     * Therefore, manually removing the entity from the registry should guarantee that it is always loaded from the
     * database.
     *
     * Code taken from https://github.com/aligent/magento2-default-async-events/
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById(int $customerId): CustomerInterface
    {
        $this->customerRegistry->remove($customerId);
        return $this->customerRepository->getById($customerId);
    }
}
