<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Quote\Model\Cart\Totals;

use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Area;
use Magento\Quote\Api\Data\TotalsItemExtensionFactory;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleTotalsItemInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleTotalsItemInterfaceFactory;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Plugin to add extension attributes gift rule data to totals item.
 *
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 */
class ItemConverterPlugin
{
    /**
     * @var GiftRuleHelper
     */
    private $giftRuleHelper;

    /**
     * @var GiftRuleTotalsItemInterfaceFactory
     */
    private $extensionAttributeFactory;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TotalsItemExtensionFactory
     */
    private $cartItemExtensionFactory;

    /**
     * @param GiftRuleHelper                     $giftRuleHelper            Gift rule helper
     * @param GiftRuleTotalsItemInterfaceFactory $extensionAttributeFactory Extension attribute factory
     * @param Image                              $imageHelper               Image helper
     * @param Emulation                          $appEmulation              App Emulation
     * @param StoreManagerInterface              $storeManager              Store Manager
     * @param TotalsItemExtensionFactory         $cartItemExtensionFactory  Cart item extension factory
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper,
        GiftRuleTotalsItemInterfaceFactory $extensionAttributeFactory,
        Image $imageHelper,
        Emulation $appEmulation,
        StoreManagerInterface $storeManager,
        TotalsItemExtensionFactory $cartItemExtensionFactory
    ) {
        $this->giftRuleHelper            = $giftRuleHelper;
        $this->extensionAttributeFactory = $extensionAttributeFactory;
        $this->imageHelper               = $imageHelper;
        $this->appEmulation              = $appEmulation;
        $this->storeManager              = $storeManager;
        $this->cartItemExtensionFactory  = $cartItemExtensionFactory;
    }

    /**
     * After model to data object: Set gift rule extension attributes to totals item.
     *
     * @param ItemConverter $subject   Subject
     * @param TotalsItemInterface $result    Result
     * @param QuoteItem   $quoteItem Quote item
     * @return TotalsItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $result,
        QuoteItem $quoteItem
    ): TotalsItemInterface {
        if ($this->giftRuleHelper->isGiftItem($quoteItem)) {
            $extensionAttributes = $this->cartItemExtensionFactory->create();
            $extensionAttributes->setGiftRule($this->getGiftRuleImageData($quoteItem));
            $result->setExtensionAttributes($extensionAttributes);
        }

        return $result;
    }

    private function getGiftRuleImageData(QuoteItem $quoteItem): GiftRuleTotalsItemInterface
    {
        $this->appEmulation->startEnvironmentEmulation(
            $this->storeManager->getStore()->getStoreId(),
            Area::AREA_FRONTEND,
            true
        );

        $imageHelper = $this->imageHelper->init($quoteItem->getProduct(), 'mini_cart_product_thumbnail');
        /** @var GiftRuleTotalsItemInterface $imageData */
        $imageData = $this->extensionAttributeFactory->create();
        $imageData
            ->setImageSrc($imageHelper->getUrl())
            ->setImageAlt($imageHelper->getLabel())
            ->setImageWidth($imageHelper->getWidth())
            ->setImageHeight($imageHelper->getHeight());

        $this->appEmulation->stopEnvironmentEmulation();

        return $imageData;
    }
}
