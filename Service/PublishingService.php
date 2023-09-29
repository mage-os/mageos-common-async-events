<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Service;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class PublishingService
{
    public function __construct(
        private readonly Json $json,
        private readonly PublisherInterface $publisher
    ) {
    }

    public function publish(string $eventName, array $arguments): void
    {
        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            [
                $eventName,
                $this->json->serialize($arguments)
            ]
        );
    }
}
