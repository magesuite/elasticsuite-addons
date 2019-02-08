<?php
namespace MageSuite\ElasticSuiteAddons\Plugin\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front;

class DisplaySearchbox
{
    const ELASTICSUITE_FIELDSET = 'elasticsuite_catalog_attribute_fieldset';

    public function aroundSetForm(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Closure $proceed, \Magento\Framework\Data\Form $form)
    {
        $block = $proceed($form);

        $this->appendDisplaySearchbox($subject, $form);

        return $block;
    }

    protected function appendDisplaySearchbox(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Magento\Framework\Data\Form $form)
    {
        $fieldset = $this->getElasticSuiteFieldset($subject, $form);

        if(empty($fieldset)){
            return $this;
        }

        $this->addDisplaySearchboxField($fieldset);

        return $this;
    }

    protected function getElasticSuiteFieldset(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->getElement(self::ELASTICSUITE_FIELDSET);

        if(!$fieldset){
            return null;
        }

        return $fieldset;
    }

    protected function addDisplaySearchboxField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $fieldset->addField(
            'display_searchbox',
            'select',
            [
                'name' => 'display_searchbox',
                'label' => __('Display Search Box'),
                'note' => __('Decides if search box should be displayed alongside "Show more" button when there are more filters than defined in Facet max. size setting.'),
                'values' => [
                    ['value' => '1', 'label' => __('Yes')],
                    ['value' => '0', 'label' => __('No')]
                ],
                'value' => '1'
            ],
            'facet_sort_order'
        );

        return $this;
    }
}
