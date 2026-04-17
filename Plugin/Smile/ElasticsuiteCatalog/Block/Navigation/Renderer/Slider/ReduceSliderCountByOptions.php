<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Plugin\Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Slider;

class ReduceSliderCountByOptions
{
    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {}

    public function afterGetJsonConfig(
        \Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Slider $subject,
        string $result
    ): string {

        if ($subject->getFilter()->getRequestVar() === \Magento\Catalog\Api\Data\ProductInterface::PRICE) {
            return $result;
        }

        try {
            $config = $this->serializer->unserialize($result);
            $config['maxItemsCount'] = $subject->getFilter()->getLayer()->getProductCollection()->getSize();
            return $this->serializer->serialize($config);
        } catch (\Exception $e) {
            return $result;
        }
    }
}
