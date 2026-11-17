<?php

declare(strict_types=1);

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

class CollectGiftRule implements ObserverInterface
{
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
     */
    public function __construct(
        protected CheckoutSession $checkoutSession,
        protected GiftRuleServiceInterface $giftRuleService,
        protected GiftRuleCacheHelper $giftRuleCacheHelper,
        protected GiftRuleConfigHelper $giftRuleConfigHelper,
        protected GiftRuleHelper $giftRuleHelper,
        protected CartRepositoryInterface $quoteRepository,
        protected Http $request
    ) {
    }

    /**
     * Update gift item quantity and add automatic gift item.
     *
     * @param Observer $observer Oberver
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer): void // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh
    {
        /** @var array|null $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        if ($giftRules) {
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            $ruleIds = explode(',', (string) $quote->getAppliedRuleIds());

            $saveQuote = false;

            /** @var array $newGiftRulesList */
            $newGiftRulesList = [];
            foreach ($giftRules as $giftRuleId => $giftRuleCode) {
                if (!in_array($giftRuleId, $ruleIds)) {
                    /** @var Item $item */
                    foreach ($quote->getAllItems() as $item) {
                        /** @var Option|null $option */
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
                        /** @var Option|null $option */
                        $option = $item->getOptionByCode('option_gift_rule');
                        /** @var Option|null $configurableOption */
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
                    if (
                        $this->giftRuleConfigHelper->isAutomaticAddEnabled() && count($giftItem) == 0 &&
                        count($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]) == 1
                    ) {
                        $this->giftRuleService->addGiftProducts(
                            $quote,
                            [
                                [
                                    'id' => key($giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS]),
                                    'qty' => $numberOfferedProduct,
                                ],
                            ],
                            (string) $giftRuleCode,
                            (int) $giftRuleId
                        );
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
            if (
                $saveQuote
                && !($this->request->getControllerName() == 'cart' && $this->request->getActionName() == 'add')
            ) {
                $this->quoteRepository->save($quote);
            }

            $this->checkoutSession->setGiftRules($newGiftRulesList);
        }
    }
}
