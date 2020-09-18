<?php

namespace MageSuite\ElasticSuiteAddons\Block\Cache;

class Identity extends \Magento\Framework\View\Element\AbstractBlock implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = $this->getData('identities');

        return is_array($identities) ? $identities : [];
    }
}
