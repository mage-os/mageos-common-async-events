<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {
    }

    /**
     * @see @event sales_order_creditmemo_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        $arguments = ['id' => $creditmemo->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($creditmemo);
        if ($eventIdentifier === null) {
            return;
        }
        $this->publisherService->publish(
            $eventIdentifier,
            $arguments
        );
    }

    private function getEventIdentifier(Creditmemo $order): ?string
    {
        if (empty($order->getOrigData('entity_id'))) {
            return 'sales.creditmemo.created';
        }
        return null;
    }
}
