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
namespace Smile\GiftSalesRule\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Helper\Config as GiftRuleConfigHelper;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
use Smile\GiftSalesRule\Model\Context\GiftRule as GiftRuleContext;

/**
 * Class CollectGiftRule
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class CollectGiftRule implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var GiftRuleCacheHelper
     */
    protected $giftRuleCacheHelper;

    /**
     * @var GiftRuleConfigHelper
     */
    protected $giftRuleConfigHelper;

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var GiftRuleContext
     */
    protected $giftRuleContext;

    /**
     * CollectGiftRule constructor.
     *
     * @param CheckoutSession          $checkoutSession      Checkout session
     * @param GiftRuleServiceInterface $giftRuleService      Gift rule service
     * @param GiftRuleCacheHelper      $giftRuleCacheHelper  Gift rule cache helper
     * @param GiftRuleConfigHelper     $giftRuleConfigHelper Gift rule config helper
     * @param GiftRuleHelper           $giftRuleHelper       Gift rule helper
     * @param CartRepositoryInterface  $quoteRepository      Quote repository
     * @param Http                     $request              Request
     * @param GiftRuleContext          $giftRuleContext      Gift rule context
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        GiftRuleServiceInterface $giftRuleService,
        GiftRuleCacheHelper $giftRuleCacheHelper,
        GiftRuleConfigHelper $giftRuleConfigHelper,
        GiftRuleHelper $giftRuleHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Request\Http $request,
        GiftRuleContext $giftRuleContext
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->giftRuleService = $giftRuleService;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;
        $this->giftRuleConfigHelper = $giftRuleConfigHelper;
        $this->giftRuleHelper = $giftRuleHelper;
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->giftRuleContext = $giftRuleContext;
    }

    /**
     * Update gift item quantity
     * Add automatic gift item
     *
     * @param Observer $observer Oberver
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var array $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        if ($giftRules) {
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            /** @var array $ruleIds */
            $ruleIds = explode(',', $quote->getAppliedRuleIds());

            $saveQuote = false;

            /** @var array $newGiftRulesList */
            $newGiftRulesList = [];
            foreach ($giftRules as $giftRuleId => $giftRuleCode) {
                if (!in_array($giftRuleId, $ruleIds)) {
                    /** @var Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        $option = $item->getOptionByCode('option_gift_rule');
                        if ($option && $option->getValue() == $giftRuleId) {
                            // Remove gift item.
                            $quote->deleteItem($item);
                            $saveQuote = true;
                        }
                    }
                } else {
                    $giftRuleData = $this->giftRuleCacheHelper->getCachedGiftRule($giftRuleCode);
                    if (!$giftRuleData) {
                        continue;
                    }

                    $newGiftRulesList[$giftRuleId] = $giftRuleCode;
                    $giftItem    = [];
                    $giftItemQty = 0;

                    /** @var Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        /** @var Option $option */
                        $option = $item->getOptionByCode('option_gift_rule');
                        /** @var Option $configurableOption */
                        $configurableOption = $item->getOptionByCode('simple_product');
                        if ($option && $option->getValue() == $giftRuleId && !$configurableOption) {
                            $giftItem[] = $item;
                            $giftItemQty += $item->getQty();
                        }
                    }

                    $numberOfferedProduct = $this->giftRuleHelper->getNumberOfferedProduct(
                        $quote,
                        $giftRuleData[GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT],
                        $giftRuleData[GiftRuleCacheHelper::DATA_PRICE_RANGE]
                    );

                    // If only 1 gift product available => add automatic gift product.
                    if ($this->giftRuleConfigHelper->isAutomaticAddEnabled() && count($giftItem) == 0 &&
                        count($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]) == 1) {
                        $this->giftRuleService->addGiftProducts(
                            $quote,
                            [
                                [
                                    'id' => key($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]),
                                    'qty' => $numberOfferedProduct,
                                ],
                            ],
                            $giftRuleCode,
                            $giftRuleId
                        );
                        $this->giftRuleContext->setGiftAddedAutomaticallyCount($numberOfferedProduct);
                        // In case we don't recollect totals,
                        // we need to set manually quote quantity summaries to update them.
                        // In case we recollect totals,
                        // these sets are not necessary, but they won't affect the native behavior.
                        $quote->setItemsCount($quote->getItemsCount() + 1);
                        $quote->setItemsQty((float)$quote->getItemsQty() + $numberOfferedProduct);
                        $saveQuote = true;
                    }

                    if ($giftItemQty > $numberOfferedProduct) {
                        // Delete gift item.
                        $qtyToDelete = $giftItemQty - $numberOfferedProduct;

                        foreach (array_reverse($giftItem) as $item) {
                            if ($item->getQty() > $qtyToDelete) {
                                $item->setQty($item->getQty() - $qtyToDelete);
                                $saveQuote = true;
                                break;
                            } else {
                                $qtyToDelete = $qtyToDelete - $item->getQty();
                                $parentItemId = $item->getParentItemId();
                                if ($parentItemId) {
                                    $quote->removeItem($parentItemId);
                                }
                                $quote->deleteItem($item);
                                $saveQuote = true;
                            }

                            if ($qtyToDelete == 0) {
                                break;
                            }
                        }
                    }
                }
            }

            /**
             * Save quote if it is not cart add controller and item changed
             */
            if ($saveQuote
                && !($this->request->getControllerName() == 'cart' && $this->request->getActionName() == 'add')) {
                $this->quoteRepository->save($quote);
            }

            $this->checkoutSession->setGiftRules($newGiftRulesList);
        }
    }
}
