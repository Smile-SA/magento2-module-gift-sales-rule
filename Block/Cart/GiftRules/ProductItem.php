<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Block\Cart\GiftRules;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Swatches\Helper\Data;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class GiftRules
 *
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 */
class ProductItem extends AbstractProduct
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var Data
     */
    protected $swatchHelper;

    /**
     * Cart constructor.
     *
     * @param Context $context
     * @param Data    $swatchHelper
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Data $swatchHelper,
        array $data = []
    ) {
        $this->swatchHelper = $swatchHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * Set product.
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        return $this->setData('product', $product);
    }

    /**
     * Get gift rule.
     *
     * @return GiftRuleDataInterface
     */
    public function getGiftRule(): GiftRuleDataInterface
    {
        return $this->getData('gift_rule');
    }

    /**
     * Set gift rule.
     *
     * @param GiftRuleDataInterface $giftRule
     * @return $this
     */
    public function setGiftRule(GiftRuleDataInterface $giftRule)
    {
        return $this->setData('gift_rule', $giftRule);
    }

    /**
     * Get details renderer. Either render swatch or select attributes for configurable products.
     *
     * @param string $type
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getDetailsRenderer($type = null)
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
     * Get swatch attributes data.
     *
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->getProduct());
    }

    /**
     * Get selected product quantity.
     *
     * @return int
     */
    public function getProductQty()
    {
        $qty = 0;
        $productId = $this->getProduct()->getId();
        $quoteFreeItems = $this->getGiftRule()->getQuoteItems();
        if (isset($quoteFreeItems[$productId])) {
            $qty = $quoteFreeItems[$productId];
        }

        return $qty;
    }
}
