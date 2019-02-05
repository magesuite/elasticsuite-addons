<?php
namespace MageSuite\ElasticSuiteAddons\Plugin\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front;

class SliderDisplayMode
{
    const ELASTICSUITE_DISPLAY_FIELDSET = 'elasticsuite_catalog_attribute_display_fieldset';

    const MODE_INPUTS_WITH_SLIDER = 'inputs-with-slider';
    const MODE_INPUTS_ONLY = 'inputs-only';
    const MODE_SLIDER_ONLY = 'slider-only';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->coreRegistry = $registry;
    }

    public function aroundSetForm(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Closure $proceed, \Magento\Framework\Data\Form $form)
    {
        $block = $proceed($form);

        $this->appendSliderDisplayMode($subject, $form);

        return $block;
    }

    protected function appendSliderDisplayMode(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Magento\Framework\Data\Form $form)
    {
        $fieldset = $this->getDisplayFieldset($subject, $form);

        if(empty($fieldset)){
            return $this;
        }

        $this->addDisplayModeField($fieldset);

        return $this;
    }

    protected function getDisplayFieldset(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject, \Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->getElement(self::ELASTICSUITE_DISPLAY_FIELDSET);

        if($fieldset){
            return $fieldset;
        }

        $attribute = $this->coreRegistry->registry('entity_attribute');
        if ($attribute->getBackendType() != 'decimal' and $attribute->getFrontendClass() != 'validate-number') {
            return null;
        }

        $fieldset = $form->addFieldset(
            self::ELASTICSUITE_DISPLAY_FIELDSET,
            [
                'legend'      => __('Slider Display Configuration'),
                'collapsable' => $subject->getRequest()->has('popup'),
            ],
            'elasticsuite_catalog_attribute_fieldset'
        );

        return $fieldset;
    }

    protected function addDisplayModeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset)
    {
        $fieldset->addField(
            'slider_display_mode',
            'select',
            [
                'name'  => 'slider_display_mode',
                'label' => __('Display Mode'),
                'note'  => __('Tells which elements will be displayed to the user on the storefront for selecting a range of values for this attribute.'),
                'values' => [
                    ['value' => self::MODE_INPUTS_WITH_SLIDER, 'label' => __('Inputs with slider')],
                    ['value' => self::MODE_INPUTS_ONLY, 'label' => __('Inputs only')],
                    ['value' => self::MODE_SLIDER_ONLY, 'label' => __('Slider only')]
                ],
                'value' => self::MODE_INPUTS_WITH_SLIDER
            ]
        );

        return $this;
    }
}
