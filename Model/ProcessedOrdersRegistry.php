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
    /** @var array|bool[] */
    private array $processedOrderIdentifiers = [];

    public function setOrderProcessed(OrderInterface $order): void
    {
        $this->processedOrderIdentifiers[$this->getIdentifier($order)] = true;
    }

    public function isOrderProcessed(OrderInterface $order): bool
    {
        return isset($this->processedOrderIdentifiers[$this->getIdentifier($order)]);
    }

    private function getIdentifier(OrderInterface $order)
    {
        return $order->getEntityId();
    }
}
