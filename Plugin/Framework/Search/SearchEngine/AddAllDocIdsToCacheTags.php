<?php

namespace MageSuite\ElasticSuiteAddons\Plugin\Framework\Search\SearchEngine;

class AddAllDocIdsToCacheTags
{
    protected $allowedActions = [
        'catalog_category_view'
    ];

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->layout = $layout;
        $this->request = $request;
    }

    /**
     * This plugin creates a block that adds all products tags returned from elasticsearch to X-Magento-Tags.
     * That way even if product was not displayed on page due to some out of date indexes it will be visible for varnish
     * for future full page cache cleaning when index is correctly rebuilt.
     */
    public function afterSearch(\Magento\Framework\Search\SearchEngineInterface $subject, $result)
    {
        $fullActionName = $this->request->getFullActionName();

        if (!in_array($fullActionName, $this->allowedActions)) {
            return $result;
        }

        $blockName = 'doc_ids_cache_tags';

        if (!$this->layout->hasElement($blockName)) {
            $iterator = $result->getIterator();

            if (count($iterator) > 0) {
                $tags = array_map(
                    function (\Magento\Framework\Api\Search\Document $doc) {
                        $productId = (int)$doc->getId();

                        return \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $productId;
                    },
                    $iterator->getArrayCopy()
                );

                $this->layout->createBlock(
                    \MageSuite\ElasticSuiteAddons\Block\Cache\Identity::class,
                    $blockName,
                    [
                        'data' => [
                            'identities' => $tags
                        ]
                    ]
                );
            }
        }

        return $result;
    }
}
