<?php

namespace MageSuite\ElasticSuiteAddons\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addSliderDisplayModeColumnToEavAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addDisplaySearchboxColumnToEavAttribute($setup);
        }

        $setup->endSetup();
    }

    protected function addSliderDisplayModeColumnToEavAttribute($setup)
    {
        $field = 'slider_display_mode';
        $table = $setup->getTable('catalog_eav_attribute');

        if ($setup->getConnection()->tableColumnExists($table, $field)) {
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

    protected function addDisplaySearchboxColumnToEavAttribute($setup)
    {
        $field = 'display_searchbox';
        $table = $setup->getTable('catalog_eav_attribute');

        if ($setup->getConnection()->tableColumnExists($table, $field)) {
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
                'comment'  => 'Display Search Box',
            ]
        );
    }
}
