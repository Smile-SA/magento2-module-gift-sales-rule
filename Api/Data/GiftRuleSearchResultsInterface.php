<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * GiftRule search results data interface.
 *
 * @api
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
interface GiftRuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get giftrule items.
     *
     * @return GiftRuleInterface[]
     */
    public function getItems();

    /**
     * Set giftrule items.
     *
     * @param GiftRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
