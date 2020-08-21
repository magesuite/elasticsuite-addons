<?php
namespace MageSuite\ElasticSuiteAddons\Test\Integration\Plugin\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DisplaySearchboxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testItSetsAndReturnsDisplaySearchbox()
    {
        $noOption = '0';

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'color');
        $attribute->setDisplaySearchbox($noOption);
        $attribute->save();

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'color');

        $this->assertEquals(
            $noOption,
            $attribute->getDisplaySearchbox()
        );
    }
}
