<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderShipmentSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {
    }

    /**
     * @see @event sales_order_shipment_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getData('shipment');
        $arguments = ['id' => $shipment->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($shipment);
        if ($eventIdentifier === null) {
            return;
        }
        $this->publisherService->publish(
            $eventIdentifier,
            $arguments
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
