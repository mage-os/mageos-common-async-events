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

        if ($this->isOrderNew($order)) {
            $this->publisherService->publish(
                'sales.order.created',
                $arguments
            );
        }
        if ($this->isOrderStatusUpdated($order)) {
            $this->publisherService->publish(
                'sales.order.updated',
                $arguments
            );
        }
        if ($this->isOrderPaid($order)) {
            $this->publisherService->publish(
                'sales.order.paid',
                $arguments
            );
        }
    }

    private function isOrderNew(Order $order): bool
    {
        return empty($order->getOrigData('entity_id'));
    }

    private function isOrderStatusUpdated(Order $order): bool
    {
        return ($order->getState() !== $order->getOrigData('state')) && $order->getOrigData('state');
    }

    private function isOrderPaid(Order $order): bool
    {
        return $order->getBaseTotalDue() == 0 && $order->getOrigData('base_total_due') != 0;
    }
}
