<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Invoice;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
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
     * @see @event sales_order_invoice_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        $arguments = ['id' => $invoice->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($invoice);
        if ($eventIdentifier === null) {
            return;
        }
        $data = [$eventIdentifier, $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );
    }

    private function getEventIdentifier(Invoice $order): ?string
    {
        if (empty($order->getOrigData('entity_id'))) {
            return 'sales.invoice.created';
        }
        return null;
    }
}
