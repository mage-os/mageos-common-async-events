<?php
declare(strict_types=1);

namespace MageOS\CommonAsyncEvents\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageOS\CommonAsyncEvents\Service\PublishingService;

class ProductSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        private readonly PublishingService $publisherService
    ) {
    }

    /**
     * @see @event catalog_product_save_commit_after
     */
    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');

        $arguments = ['productId' => $product->getId()];

        if ($this->isProductNew($product)) {
            $this->publisherService->publish(
                'catalog.product.created',
                $arguments
            );
        }
        if ($this->isProductUpdated($product)) {
            $this->publisherService->publish(
                'catalog.product.updated',
                $arguments
            );
        }
    }

    private function isProductNew(Product $product): bool
    {
        return empty($product->getOrigData('entity_id'));
    }

    private function isProductUpdated(Product $product): bool
    {
        return $product->hasDataChanges();
    }
}
