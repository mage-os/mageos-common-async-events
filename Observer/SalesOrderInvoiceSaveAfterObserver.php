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

        if ($this->isInvoiceCreated($invoice)) {
            $this->publisher->publish(
                QueueMetadataInterface::EVENT_QUEUE,
                [
                    'sales.invoice.created',
                    $this->json->serialize($arguments)
                ]
            );
        }

        if ($this->isInvoicePaid($invoice)) {
            $this->publisher->publish(
                QueueMetadataInterface::EVENT_QUEUE,
                [
                    'sales.invoice.paid',
                    $this->json->serialize($arguments)
                ]
            );
        }
    }

    private function isInvoiceCreated(Invoice $invoice): bool
    {
        return empty($invoice->getOrigData('entity_id'));
    }

    private function isInvoicePaid(Invoice $invoice): bool
    {
        return $invoice->getState() === Invoice::STATE_PAID
            && $invoice->getOrigData('state') !== Invoice::STATE_PAID;
    }
}
