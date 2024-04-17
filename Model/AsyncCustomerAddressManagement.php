<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Model\AddressRegistry as CustomerAddressRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AsyncCustomerAddressManagement
{
    private CustomerAddressRepositoryInterface $customerAddressRepository;
    private CustomerAddressRegistry $customerAddressRegistry;

    public function __construct(
        CustomerAddressRepositoryInterface $customerAddressRepository,
        CustomerAddressRegistry $customerAddressRegistry
    ) {
        $this->customerAddressRepository = $customerAddressRepository;
        $this->customerAddressRegistry = $customerAddressRegistry;
    }

    /**
     * The CustomerAddressRepository uses a CustomerAddressRegistry which will cache entities on load. If the updates
     * happen in a different thread, there is a possibility that stale data is returned.
     * However, we still want to use the repository instead of using the resource model to preserve modifications
     * added by plugins.
     *
     * Therefore, manually removing the entity from the registry should guarantee that it is always loaded from the
     * database.
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById(int $addressId): CustomerAddressInterface
    {
        $this->customerAddressRegistry->remove($addressId);
        return $this->customerAddressRepository->getById($addressId);
    }
}
