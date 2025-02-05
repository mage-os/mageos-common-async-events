<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Save a list of already processed order events during this request so they don't get executed twice.
 */
class ProcessedOrderEventsRegistry
{
    /** @var array|bool[] */
    private array $processedOrderIdentifiers = [];

    public function setEventProcessed(OrderInterface $order, string $eventName): void
    {
        $this->processedOrderIdentifiers[$this->getIdentifier($order)][$eventName] = true;
    }

    public function isEventProcessed(OrderInterface $order, string $eventName): bool
    {
        return isset($this->processedOrderIdentifiers[$this->getIdentifier($order)][$eventName]);
    }

    private function getIdentifier(OrderInterface $order)
    {
        return $order->getEntityId();
    }
}
