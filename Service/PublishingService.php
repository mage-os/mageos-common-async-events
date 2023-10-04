<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Service;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class PublishingService
{
    public function __construct(
        private readonly Json $json,
        private readonly PublisherInterface $publisher,
        private readonly AsyncEventRepositoryInterface $asyncEventRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function publish(string $eventName, array $arguments): void
    {
        if (!$this->canSendEvent($eventName)) {
            return;
        }
        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            [
                $eventName,
                $this->json->serialize($arguments)
            ]
        );
    }

    private function canSendEvent(string $eventName): bool
    {
        $configuredEvents = $this->asyncEventRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('status', 1)
                ->create()
        )->getItems();
        $configuredEventNames = array_map(
            function (AsyncEventInterface $event): string {
                return $event->getEventName();
            },
            $configuredEvents
        );
        return in_array($eventName, $configuredEventNames);
    }
}
