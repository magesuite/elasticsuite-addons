<?php

namespace MageSuite\ElasticSuiteAddons\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addSliderDisplayModeCollumnToEavAttribute($setup);
        }

        $setup->endSetup();
    }

    protected function addSliderDisplayModeCollumnToEavAttribute($setup)
    {
        $field = 'slider_display_mode';
        $table = $setup->getTable('catalog_eav_attribute');

        if($setup->getConnection()->tableColumnExists($table, $field) !== false){
            return;
        }

        $setup->getConnection()->addColumn(
            $table,
            $field,
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default'  => null,
                'length'   => 20,
                'comment'  => 'Slider Display Mode',
            ]
        );
    }
}