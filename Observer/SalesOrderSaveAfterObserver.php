<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MageOS\CommonAsyncEvents\Model\ProcessedOrderEventsRegistry;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService,
        private readonly ProcessedOrderEventsRegistry $processedOrderEventsRegistry
    ) {
    }

    /**
     * @see @event sales_order_save_commit_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');

        $arguments = ['id' => $order->getId()];

        if ($this->isOrderNew($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.created')) {
            $this->publisherService->publish(
                'sales.order.created',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.created');
        }
        if ($this->isOrderStatusUpdated($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.created')
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.updated')) {
            $this->publisherService->publish(
                'sales.order.updated',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.updated');
        }
        if ($this->isOrderPaid($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.paid')) {
            $this->publisherService->publish(
                'sales.order.paid',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.paid');
        }
        if ($this->isOrderHolded($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.holded')) {
            $this->publisherService->publish(
                'sales.order.holded',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.holded');
        }
        if ($this->isOrderUnholded($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.unholded')) {
            $this->publisherService->publish(
                'sales.order.unholded',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.unholded');
        }
        if ($this->isOrderCancelled($order)
            && !$this->processedOrderEventsRegistry->isEventProcessed($order, 'sales.order.cancelled')) {
            $this->publisherService->publish(
                'sales.order.cancelled',
                $arguments
            );
            $this->processedOrderEventsRegistry->setEventProcessed($order, 'sales.order.cancelled');
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
        return $order->getBaseTotalDue() == 0 &&
            ($this->isOrderNew($order) || $order->getOrigData('base_total_due') != 0);
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
