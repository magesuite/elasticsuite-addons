<?php

namespace MageSuite\ElasticSuiteAddons\Rule\Condition\Product\SpecialAttribute;

/**
 * Special "is_new" attribute class.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogRule
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class IsNew implements \Smile\ElasticsuiteCatalogRule\Api\Rule\Condition\Product\SpecialAttributeInterface
{
    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanSource;

    /**
     * HasImage constructor.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory  Query Factory
     * @param \Magento\Config\Model\Config\Source\Yesno                 $booleanSource Boolean Source
     */
    public function __construct(
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \Magento\Config\Model\Config\Source\Yesno $booleanSource
    ) {
        $this->queryFactory  = $queryFactory;
        $this->booleanSource = $booleanSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return 'is_new';
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQuery(\Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product $condition = null)
    {
        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $clauses = [];

        $newFromDateEarlier = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_RANGE,
            ['field' => 'news_from_date', 'bounds' => ['lte' => $now]]
        );

        $newsToDateLater = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_RANGE,
            ['field' => 'news_to_date', 'bounds' => ['gte' => $now]]
        );

        $missingNewsFromDate = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_MISSING,
            ['field' => 'news_from_date']
        );
        $missingNewsToDate   = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_MISSING,
            ['field' => 'news_to_date']
        );

        $clauses[] = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_BOOL,
            ['must' => [$newFromDateEarlier, $missingNewsToDate]]
        );

        $clauses[] = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_BOOL,
            ['must' => [$missingNewsFromDate, $newsToDateLater]]
        );

        $clauses[] = $this->queryFactory->create(
            \Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_BOOL,
            ['must' => [$newFromDateEarlier, $newsToDateLater]]
        );

        return $this->queryFactory->create(\Smile\ElasticsuiteCore\Search\Request\QueryInterface::TYPE_BOOL, ['should' => $clauses]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperatorName()
    {
        return ' ';
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueName($value = null)
    {
        return ' ';
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value = null)
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueOptions()
    {
        return $this->booleanSource->toOptionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Only new products');
    }
}
