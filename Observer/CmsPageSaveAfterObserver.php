<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class CmsPageSaveAfterObserver implements ObserverInterface
{
    /**
     * @param PublishingService $publisherService
     */
    public function __construct(
        private readonly PublishingService $publisherService
    ) {}

    /**
     * @see @event magento_cms_api_data_pageinterface_save_after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $page = $this->getPage($observer);

        if (!$page instanceof Page || !$page->getId()) {
            return;
        }

        $pageId = (int)$page->getId();

        if ($this->isPageNew($page)) {
            $this->publisherService->publish('cms.page.created', ['pageId' => $pageId]);
            return;
        }

        if ($this->isPageUpdated($page)) {
            $this->publisherService->publish('cms.page.updated', ['pageId' => $pageId]);
        }
    }

    /**
     * @param Observer $observer
     * @return mixed
     */
    private function getPage(Observer $observer): mixed
    {
        return $observer->getEvent()->getData('object')
            ?? $observer->getEvent()->getData('data_object')
            ?? $observer->getEvent()->getData('entity');
    }

    /**
     * @param Page $page
     * @return bool
     */
    private function isPageNew(Page $page): bool
    {
        return empty($page->getOrigData('page_id'));
    }

    /**
     * @param Page $page
     * @return bool
     */
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

    /**
     * @param Page $page
     * @param string $fieldName
     * @param string $getter
     * @return bool
     */
    private function hasFieldChanged(Page $page, string $fieldName, string $getter): bool
    {
        return $page->$getter() !== $page->getOrigData($fieldName);
    }
}
