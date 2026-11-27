<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * GiftRule search results data interface.
 *
 * @api
 * @copyright 2019 Smile
 */
interface GiftRuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get giftrule items.
     *
     * @return \Smile\GiftSalesRule\Api\Data\GiftRuleInterface[]
     */
    public function getItems();

    /**
     * Set giftrule items.
     *
     * @param \Smile\GiftSalesRule\Api\Data\GiftRuleInterface[] $items Gift rules
     * @return $this
     */
    public function setItems(array $items);
}
