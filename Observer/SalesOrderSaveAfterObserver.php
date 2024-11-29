<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MageOS\CommonAsyncEvents\Model\ProcessedOrdersRegistry;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService,
        private readonly ProcessedOrdersRegistry $processedOrdersRegistry
    ) {
    }

    /**
     * @see @event sales_order_save_commit_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($this->processedOrdersRegistry->isOrderProcessed($order)) {
            return;
        }

        $arguments = ['id' => $order->getId()];

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
        if ($this->isOrderHolded($order)) {
            $this->publisherService->publish(
                'sales.order.holded',
                $arguments
            );
        }
        if ($this->isOrderUnholded($order)) {
            $this->publisherService->publish(
                'sales.order.unholded',
                $arguments
            );
        }
        if ($this->isOrderCancelled($order)) {
            $this->publisherService->publish(
                'sales.order.cancelled',
                $arguments
            );
        }

        $this->processedOrdersRegistry->setOrderProcessed($order);
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

    private function isOrderHolded(Order $order): bool
    {
        return ($order->getState() == Order::STATE_HOLDED) && $order->getOrigData('state') != Order::STATE_HOLDED;
    }

    private function isOrderUnholded(Order $order): bool
    {
        return ($order->getState() != Order::STATE_HOLDED) && $order->getOrigData('state') == Order::STATE_HOLDED;
    }

    private function isOrderCancelled(Order $order): bool
    {
        return ($order->isCanceled()) && $order->getOrigData('state') != Order::STATE_CANCELED;
    }
}
