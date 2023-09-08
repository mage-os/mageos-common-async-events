<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    private Json $json;
    private PublisherInterface $publisher;

    public function __construct(
        Json $json,
        PublisherInterface $publisher
    ) {
        $this->json = $json;
        $this->publisher = $publisher;
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
        $data = [$eventIdentifier, $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
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
