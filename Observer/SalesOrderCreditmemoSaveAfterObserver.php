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
     * @see @event sales_order_creditmemo_save_commit_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        if ($this->isCreditmemoCreated($creditmemo)) {
            $this->publisherService->publish(
                'sales.creditmemo.created',
                ['id' => $creditmemo->getId()]
            );
        }
    }

    private function isCreditmemoCreated(Creditmemo $creditmemo): bool
    {
        return empty($creditmemo->getOrigData('entity_id'));
    }
}
