<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {
    }

    /**
     * @see @event sales_order_invoice_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        $arguments = ['id' => $invoice->getId()];

        if ($this->isInvoiceCreated($invoice)) {
            $this->publisherService->publish(
                'sales.invoice.created',
                $arguments
            );
        }

        if ($this->isInvoicePaid($invoice)) {
            $this->publisherService->publish(
                'sales.invoice.paid',
                $arguments
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
