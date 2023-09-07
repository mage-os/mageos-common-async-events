<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class CustomerSaveAfterObserver implements ObserverInterface
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

    public function execute(Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');
        $arguments = ['customerId' => $customer->getId()];
        $data = ['customer.updated', $this->json->serialize($arguments)];

        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            $data
        );
    }
}
