<?php

declare(strict_types=1);

namespace MageSuite\ElasticSuiteAddons\Test\Integration\Plugin\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData;

use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use MageSuite\ElasticSuiteAddons\Helper\Configuration\LayeredNavigation;

class IndexLowestChildValueOfDecimalAttributesTest extends \PHPUnit\Framework\TestCase
{
    protected const string DECIMAL_ATTRIBUTE_CODE = 'slider_test_width';

    protected const array DECIMAL_ATTRIBUTE_DATA = [
        'attribute_code' => self::DECIMAL_ATTRIBUTE_CODE,
        'backend_type' => 'decimal',
        'frontend_input' => 'price',
        'is_filterable' => 1,
    ];

    protected const array FIRST_CHILD_DATA = ['custom_attributes' => [['attribute_code' => self::DECIMAL_ATTRIBUTE_CODE, 'value' => 1500]]];

    protected const array SECOND_CHILD_DATA = ['custom_attributes' => [['attribute_code' => self::DECIMAL_ATTRIBUTE_CODE, 'value' => 900]]];

    protected const array CONFIGURABLE_PRODUCT_DATA = [
        '_options' => ['$configurable_attribute$'],
        '_links' => ['$first_child$', '$second_child$'],
        'custom_attributes' => [['attribute_code' => self::DECIMAL_ATTRIBUTE_CODE, 'value' => 100]],
    ];

    protected ?\Magento\Framework\ObjectManagerInterface $objectManager;

    protected ?\Magento\TestFramework\Fixture\DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fixtures = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        Config(LayeredNavigation::XML_PATH_INDEX_LOWEST_CHILD_VALUE_OF_DECIMAL_ATTRIBUTES, 1, 'store', 'default'),
        DataFixture(AttributeFixture::class, self::DECIMAL_ATTRIBUTE_DATA, 'decimal_attribute'),
        DataFixture(ConfigurableAttributeFixture::class, as: 'configurable_attribute'),
        DataFixture(ProductFixture::class, self::FIRST_CHILD_DATA, 'first_child'),
        DataFixture(ProductFixture::class, self::SECOND_CHILD_DATA, 'second_child'),
        DataFixture(ConfigurableProductFixture::class, self::CONFIGURABLE_PRODUCT_DATA, 'configurable_product'),
    ]
    public function testConfigurableProductIsIndexedWithLowestChildValueInsteadOfItsOwnValue(): void
    {
        $this->assertSame([900.0], $this->getIndexedDecimalValues());
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        Config(LayeredNavigation::XML_PATH_INDEX_LOWEST_CHILD_VALUE_OF_DECIMAL_ATTRIBUTES, 0, 'store', 'default'),
        DataFixture(AttributeFixture::class, self::DECIMAL_ATTRIBUTE_DATA, 'decimal_attribute'),
        DataFixture(ConfigurableAttributeFixture::class, as: 'configurable_attribute'),
        DataFixture(ProductFixture::class, self::FIRST_CHILD_DATA, 'first_child'),
        DataFixture(ProductFixture::class, self::SECOND_CHILD_DATA, 'second_child'),
        DataFixture(ConfigurableProductFixture::class, self::CONFIGURABLE_PRODUCT_DATA, 'configurable_product'),
    ]
    public function testIndexDataIsLeftUnchangedWhenDisabled(): void
    {
        $storeId = (int)$this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore('default')->getId();
        $configurableProductId = (int)$this->fixtures->get('configurable_product')->getId();
        $childIds = [(int)$this->fixtures->get('first_child')->getId(), (int)$this->fixtures->get('second_child')->getId()];
        $indexData = [$configurableProductId => ['children_ids' => $childIds, self::DECIMAL_ATTRIBUTE_CODE => [100.0]]];
        $plugin = $this->objectManager->create(
            \MageSuite\ElasticSuiteAddons\Plugin\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData\IndexLowestChildValueOfDecimalAttributes::class
        );
        $subject = $this->createMock(\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData::class);

        $this->assertSame($indexData, $plugin->afterAddData($subject, $indexData, $storeId));
    }

    protected function getIndexedDecimalValues(): array
    {
        $storeId = (int)$this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore('default')->getId();
        $configurableProductId = (int)$this->fixtures->get('configurable_product')->getId();
        $attributeData = $this->objectManager->create(\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData::class);
        $indexData = $attributeData->addData($storeId, [$configurableProductId => ['type_id' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE]]);

        return $indexData[$configurableProductId][self::DECIMAL_ATTRIBUTE_CODE] ?? [];
    }
}
