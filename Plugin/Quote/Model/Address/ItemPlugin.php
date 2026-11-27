<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Quote\Model\Address;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Plugin Address Item: To remove when the PR https://github.com/magento/magento2/pull/31309 is merged.
 *
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportQuoteItem(
        AddressItem $subject,
        AddressItem $result,
        QuoteItem $quoteItem
    ): AddressItem {
        $result->setCustomPrice($quoteItem->getCustomPrice());
        $result->setOriginalCustomPrice($quoteItem->getOriginalCustomPrice());

        return $result;
    }
}
