<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Creditmemo;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
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
     * @see @event sales_order_creditmemo_save_after
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        $arguments = ['id' => $creditmemo->getIncrementId()];

        $eventIdentifier = $this->getEventIdentifier($creditmemo);
        if ($eventIdentifier === null) {
            return;
        }
        $data = [$eventIdentifier, $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
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
