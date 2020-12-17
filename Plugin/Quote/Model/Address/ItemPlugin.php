<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Quote\Model\Address;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Plugin Address Item: To remove when the PR https://github.com/magento/magento2/pull/31309 is merged.
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 */
class ItemPlugin
{
    /**
     * After import quote item: Set custom prices to the quote address item.
     *
     * @param AddressItem $subject   Subject
     * @param AddressItem $result    Result
     * @param QuoteItem   $quoteItem Quote item
     * @return AddressItem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportQuoteItem(
        AddressItem $subject,
        AddressItem $result,
        QuoteItem $quoteItem
    ): AddressItem {
        return $result
            ->setCustomPrice($quoteItem->getCustomPrice())
            ->setOriginalCustomPrice($quoteItem->getOriginalCustomPrice());
    }
}
