<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Block\Cart\GiftRules;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Swatches\Helper\Data;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;

/**
 * Class GiftRules
 *
 * @copyright 2019 Smile
 */
class ProductItem extends AbstractProduct
{
    /**
     * Cart constructor.
     *
     * @param Context $context      Context
     * @param Data    $swatchHelper Swatch helper
     * @param array   $data         Data
     */
    public function __construct(
        Context $context,
        protected Data $swatchHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get product.
     */
    public function getProduct(): ?Product
    {
        return $this->getData('product');
    }

    /**
     * Set product.
     *
     * @param Product $product Product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        return $this->setData('product', $product);
    }

    /**
     * Get gift rule.
     */
    public function getGiftRule(): ?GiftRuleDataInterface
    {
        return $this->getData('gift_rule');
    }

    /**
     * Set gift rule.
     *
     * @param GiftRuleDataInterface $giftRule Gift rule
     * @return $this
     */
    public function setGiftRule(GiftRuleDataInterface $giftRule)
    {
        return $this->setData('gift_rule', $giftRule);
    }

    /**
     * Get details renderer. Either render swatch or select attributes for configurable products.
     *
     * @param string|null $type Type
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getDetailsRenderer(mixed $type = null): bool|AbstractBlock|null
    {
        if ($type === null) {
            $type = 'default';
        }

        if ($type === Configurable::TYPE_CODE) {
            $swatchAttributesData = $this->getSwatchAttributesData();
            if (empty($swatchAttributesData)) {
                return $this->getChildBlock('configurable-details');
            } else {
                return $this->getChildBlock('swatch-details');
            }
        }

        return parent::getDetailsRenderer($type);
    }

    /**
     * Get selected product quantity (quote item qty, may be a numeric string when loaded from DB).
     */
    public function getProductQty(): float|int|string
    {
        $qty = 0;
        $productId = $this->getProduct()->getId();
        $quoteFreeItems = $this->getGiftRule()->getQuoteItems();
        if (isset($quoteFreeItems[$productId])) {
            $qty = $quoteFreeItems[$productId];
        }

        return $qty;
    }

    /**
     * Get swatch attributes data.
     */
    protected function getSwatchAttributesData(): array
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->getProduct());
    }
}
