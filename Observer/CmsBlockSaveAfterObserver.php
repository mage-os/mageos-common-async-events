<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Cms\Model\Block;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CmsBlockSaveAfterObserver implements ObserverInterface
{
    /**
     * @param PublishingService $publisherService
     */
    public function __construct(
        private readonly PublishingService $publisherService
    ) {}

    /**
     * @see @event magento_cms_api_data_blockinterface_save_after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $block = $this->getBlock($observer);

        if (!$block instanceof Block || !$block->getId()) {
            return;
        }

        $blockId = (int)$block->getId();

        if ($this->isBlockNew($block)) {
            $this->publisherService->publish('cms.block.created', ['blockId' => $blockId]);
            return;
        }

        if ($this->isBlockUpdated($block)) {
            $this->publisherService->publish('cms.block.updated', ['blockId' => $blockId]);
        }
    }

    /**
     * @param Observer $observer
     * @return mixed
     */
    private function getBlock(Observer $observer): mixed
    {
        return $observer->getEvent()->getData('object')
            ?? $observer->getEvent()->getData('data_object')
            ?? $observer->getEvent()->getData('entity');
    }

    /**
     * @param Block $block
     * @return bool
     */
    private function isBlockNew(Block $block): bool
    {
        return empty($block->getOrigData('block_id'));
    }

    /**
     * @param Block $block
     * @return bool
     */
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

    /**
     * @param Block $block
     * @param string $fieldName
     * @param string $getter
     * @return bool
     */
    private function hasFieldChanged(Block $block, string $fieldName, string $getter): bool
    {
        return $block->$getter() !== $block->getOrigData($fieldName);
    }
}
