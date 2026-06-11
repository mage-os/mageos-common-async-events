<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Cms\Model\Block;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CmsBlockSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {}

    /**
     * @see @event magento_cms_api_data_blockinterface_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Block $block */
        $block = $observer->getEvent()->getData('entity');
        $blockId = $block->getId();

        // New blocks should not have the status "updated" at the same time.
        if ($this->isBlockNew($block)) {
            $this->publisherService->publish('cms.block.created', ['blockId' => $blockId]);
            return;
        }

        if ($this->isBlockUpdated($block)) {
            $this->publisherService->publish('cms.block.updated', ['blockId' => $blockId]);
        }
    }

    private function isBlockNew(Block $block): bool
    {
        return empty($block->getOrigData('block_id'));
    }

    private function isBlockUpdated(Block $block): bool
    {
        $fields = [
            ['content', 'getContent'],
            ['identifier', 'getIdentifier'],
            ['title', 'getTitle'],
        ];

        foreach ($fields as [$field, $getter]) {
            if ($this->hasFieldChanged($block, $field, $getter)) {
                return true;
            }
        }

        return ($block->getUpdateTime() !== null && $block->getUpdateTime() !== $block->getOrigData('update_time'))
            || $block->hasDataChanges();
    }

    private function hasFieldChanged(Block $block, string $fieldName, string $getter): bool
    {
        return $block->$getter() !== $block->getOrigData($fieldName);
    }
}
