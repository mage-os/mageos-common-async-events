<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CmsPageSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {}

    /**
     * @see @event magento_cms_api_data_pageinterface_save_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Page $page */
        $page = $observer->getEvent()->getData('entity');
        $pageId = $page->getId();

        // New pages should not have the status "updated" at the same time.
        if ($this->isPageNew($page)) {
            $this->publisherService->publish('cms.page.created', ['pageId' => $pageId]);
            return;
        }

        if ($this->isPageUpdated($page)) {
            $this->publisherService->publish('cms.page.updated', ['pageId' => $pageId]);
        }
    }

    private function isPageNew(Page $page): bool
    {
        return empty($page->getOrigData('page_id'));
    }

    private function isPageUpdated(Page $page): bool
    {
        $fields = [
            ['content', 'getContent'],
            ['identifier', 'getIdentifier'],
            ['meta_description', 'getMetaDescription'],
            ['meta_title', 'getMetaTitle'],
        ];

        foreach ($fields as [$field, $getter]) {
            if ($this->hasFieldChanged($page, $field, $getter)) {
                return true;
            }
        }

        return ($page->getUpdateTime() !== null && $page->getUpdateTime() !== $page->getOrigData('update_time'))
            || $page->hasDataChanges();
    }

    private function hasFieldChanged(Page $page, string $fieldName, string $getter): bool
    {
        return $page->$getter() !== $page->getOrigData($fieldName);
    }
}
