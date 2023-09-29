<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {
    }

    /**
     * @see @event sales_order_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $arguments = ['id' => $order->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($order);
        if ($eventIdentifier === null) {
            return;
        }
        $this->publisherService->publish(
            $eventIdentifier,
            $arguments
        );
    }

    private function getEventIdentifier(Order $order): ?string
    {
        if (empty($order->getOrigData('entity_id'))) {
            return 'sales.order.created';
        }
        if ($order->getState() !== $order->getOrigData('state')) {
            return 'sales.order.updated';
        }
        return null;
    }
}
