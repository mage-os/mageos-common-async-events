<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Save a list of already processed orders during this request.
 */
class ProcessedOrdersRegistry
{
    const FIELD_NAME_IDENTIFIER = 'entity_id';

    /** @var array|string[] */
    private array $processedOrderIdentifiers = [];

    public function setOrderProcessed(OrderInterface $order): void
    {
        $this->processedOrderIdentifiers[$this->getIdentifier($order)] = true;
    }

    public function isOrderProcessed(OrderInterface $order): bool
    {
        $identifier = $this->getIdentifier($order);

        if (isset($this->processedOrderIdentifiers[$identifier])) {
            unset ($this->processedOrderIdentifiers[$identifier]);
            return true;
        }
        return false;
    }

    private function getIdentifier(OrderInterface $order)
    {
        return $order->getEntityId();
    }
}
