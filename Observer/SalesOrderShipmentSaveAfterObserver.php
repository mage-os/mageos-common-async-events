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
        if ($this->isShipmentNew($shipment)) {
            $this->publisherService->publish(
                'sales.shipment.created',
                ['id' => $shipment->getId()]
            );
        }
        if ($this->isOrderFullyShipped($shipment)) {
            $this->publisherService->publish(
                'sales.order.shipped',
                ['id' => $shipment->getOrderId()]
            );
        }
    }

    private function isShipmentNew(Shipment $shipment): bool
    {
        return empty($shipment->getOrigData('entity_id'));
    }

    private function isOrderFullyShipped(Shipment $shipment): bool
    {
        return $this->isShipmentNew($shipment) && !$shipment->getOrder()->canShip();
    }
}
