<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Test\Unit\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\Storage;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Helper\Config as GiftRuleConfigHelper;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
use Smile\GiftSalesRule\Observer\CollectGiftRule;

/**
 * Gift items of a quote are kept within the number of products the applied gift rule offers.
 */
class CollectGiftRuleTest extends TestCase
{
    private const RULE_ID = 12;
    private const MAXIMUM_NUMBER_PRODUCT = 1;

    private Storage $sessionStorage;
    private Quote|MockObject $quote;
    private CollectGiftRule $observer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionStorage = new Storage('checkout');
        $checkoutSession = (new ReflectionClass(CheckoutSession::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(SessionManager::class, 'storage'))->setValue($checkoutSession, $this->sessionStorage);

        $cacheHelper = $this->createStub(GiftRuleCacheHelper::class);
        $cacheHelper->method('getCachedGiftRule')->willReturn([
            GiftRuleCacheHelper::DATA_PRODUCT_ITEMS => [42 => 'sku'],
            GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT => self::MAXIMUM_NUMBER_PRODUCT,
            GiftRuleCacheHelper::DATA_PRICE_RANGE => null,
        ]);
        $configHelper = $this->createStub(GiftRuleConfigHelper::class);
        $configHelper->method('isAutomaticAddEnabled')->willReturn(false);

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems', 'deleteItem'])
            ->getMock();

        $this->observer = new CollectGiftRule(
            $checkoutSession,
            $this->createStub(GiftRuleServiceInterface::class),
            $cacheHelper,
            $configHelper,
            (new ReflectionClass(GiftRuleHelper::class))->newInstanceWithoutConstructor(),
            $this->createStub(CartRepositoryInterface::class),
            $this->createStub(Http::class)
        );
    }

    /**
     * A configurable gift whose quantity was raised in the cart is brought back to the offered number.
     */
    public function testConfigurableGiftQtyAboveOfferedNumberIsTrimmed(): void
    {
        $parent = $this->createGiftItem(self::RULE_ID, 5, ['simple_product' => '43']);
        $child = $this->createGiftItem(self::RULE_ID, 1);
        $child->setParentItem($parent);

        $parent->expects($this->once())->method('setQty')->with(self::MAXIMUM_NUMBER_PRODUCT);
        $child->expects($this->never())->method('setQty');
        $this->quote->expects($this->never())->method('deleteItem');

        $this->collect([$parent, $child]);
    }

    /**
     * A simple gift whose quantity was raised in the cart is brought back to the offered number.
     */
    public function testSimpleGiftQtyAboveOfferedNumberIsTrimmed(): void
    {
        $item = $this->createGiftItem(self::RULE_ID, 3);

        $item->expects($this->once())->method('setQty')->with(self::MAXIMUM_NUMBER_PRODUCT);
        $this->quote->expects($this->never())->method('deleteItem');

        $this->collect([$item]);
    }

    /**
     * A gift within the offered number is left untouched.
     */
    public function testGiftWithinOfferedNumberIsKept(): void
    {
        $parent = $this->createGiftItem(self::RULE_ID, 1, ['simple_product' => '43']);
        $child = $this->createGiftItem(self::RULE_ID, 1);
        $child->setParentItem($parent);

        $parent->expects($this->never())->method('setQty');
        $child->expects($this->never())->method('setQty');
        $this->quote->expects($this->never())->method('deleteItem');

        $this->collect([$parent, $child]);
    }

    /**
     * A bundle gift counts its own qty once, whatever the qty of its selections.
     */
    public function testBundleGiftCountsParentQtyOnly(): void
    {
        $parent = $this->createGiftItem(self::RULE_ID, 1, ['bundle_identity' => '50_1']);
        $firstSelection = $this->createGiftItem(self::RULE_ID, 2);
        $firstSelection->setParentItem($parent);
        $secondSelection = $this->createGiftItem(self::RULE_ID, 3);
        $secondSelection->setParentItem($parent);

        $parent->expects($this->never())->method('setQty');
        $firstSelection->expects($this->never())->method('setQty');
        $secondSelection->expects($this->never())->method('setQty');
        $this->quote->expects($this->never())->method('deleteItem');

        $this->collect([$parent, $firstSelection, $secondSelection]);
    }

    /**
     * A child item whose parent object is not attached is still recognised as a child by its parent item id.
     */
    public function testChildWithDetachedParentIsNotCounted(): void
    {
        $parent = $this->createGiftItem(self::RULE_ID, 1, ['simple_product' => '43']);
        $child = $this->createGiftItem(self::RULE_ID, 1);
        $child->setData('parent_item_id', 7);

        $parent->expects($this->never())->method('setQty');
        $child->expects($this->never())->method('setQty');
        $this->quote->expects($this->never())->method('deleteItem');

        $this->collect([$parent, $child]);
    }

    /**
     * Gift lines of one rule above the offered number are deleted from the last one.
     */
    public function testLastGiftLineAboveOfferedNumberIsDeleted(): void
    {
        $first = $this->createGiftItem(self::RULE_ID, 1);
        $last = $this->createGiftItem(self::RULE_ID, 1);

        $first->expects($this->never())->method('setQty');
        $last->expects($this->never())->method('setQty');
        $this->quote->expects($this->once())->method('deleteItem')->with($this->identicalTo($last));

        $this->collect([$first, $last]);
    }

    /**
     * A gift item whose rule is not applied to the quote is removed, even with an empty session.
     */
    public function testGiftOfUnappliedRuleIsRemoved(): void
    {
        $item = $this->createGiftItem(self::RULE_ID, 1);
        $this->sessionStorage->setData('gift_rules', []);
        $this->quote->setData('applied_rule_ids', '3');
        $this->quote->method('getAllItems')->willReturn([$item]);

        $item->expects($this->never())->method('setQty');
        $this->quote->expects($this->once())->method('deleteItem')->with($this->identicalTo($item));

        $this->observer->execute(new Observer(['event' => new Event(['quote' => $this->quote])]));
    }

    /**
     * Run the observer on the quote items, with the gift rule applied and held in the session.
     */
    private function collect(array $items): void
    {
        $this->sessionStorage->setData('gift_rules', [self::RULE_ID => (string) self::RULE_ID]);
        $this->quote->setData('applied_rule_ids', '3,' . self::RULE_ID);
        $this->quote->method('getAllItems')->willReturn($items);

        $this->observer->execute(new Observer(['event' => new Event(['quote' => $this->quote])]));
    }

    /**
     * Create a quote item flagged as a gift of the given rule.
     */
    private function createGiftItem(int $giftRuleId, float $qty, array $extraOptions = []): Item|MockObject
    {
        $options = ['option_gift_rule' => (string) $giftRuleId] + $extraOptions;

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptionByCode', 'setQty'])
            ->getMock();
        $item->setData('qty', $qty);
        $item->method('getOptionByCode')->willReturnCallback(
            fn (string $code) => isset($options[$code]) ? $this->createOption($options[$code]) : null
        );

        return $item;
    }

    /**
     * Create a quote item option holding the given value.
     */
    private function createOption(string $value): Option
    {
        $option = (new ReflectionClass(Option::class))->newInstanceWithoutConstructor();
        $option->setData('value', $value);

        return $option;
    }
}
