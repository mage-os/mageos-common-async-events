<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Shipment;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class SalesOrderShipmentSaveAfterObserver implements ObserverInterface
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
     * @see @event sales_order_shipment_save_after
     */
    public function execute(Observer $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getData('shipment');
        $arguments = ['id' => $shipment->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($shipment);
        if ($eventIdentifier === null) {
            return;
        }
        $data = [$eventIdentifier, $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );
    }

    private function getEventIdentifier(Shipment $order): ?string
    {
        if (empty($order->getOrigData('entity_id'))) {
            return 'sales.shipment.created';
        }
        return null;
    }
}
