<?php

include BP.'/dev/tests/integration/testsuite/Magento/Catalog/_files/category.php';

foreach ($products as $data) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
    $product
        ->setTypeId('simple')
        ->setId($data['id'])
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName($data['name'])
        ->setSku($data['sku'])
        ->setUrlKey($data['sku'])
        ->setPrice(10)
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setCategoryIds([333])
        ->save();
    $product->reindex();
    $product->priceReindexCallback();
}
