<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Model\ResourceModel\GiftRule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Model\GiftRule;

/**
 * GiftRule collection.
 *
 * @copyright 2019 Smile
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray(GiftRuleInterface::RULE_ID, GiftRuleInterface::RULE_ID);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            GiftRule::class,
            \Smile\GiftSalesRule\Model\ResourceModel\GiftRule::class
        );
    }
}
