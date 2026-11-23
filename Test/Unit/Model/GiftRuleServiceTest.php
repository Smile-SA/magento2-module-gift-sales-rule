<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Test\Unit\Model;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\Storage;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
use Smile\GiftSalesRule\Model\GiftRuleData;
use Smile\GiftSalesRule\Model\GiftRuleService;

/**
 * Gift products can only be added for a gift rule held in the session and applied to the quote.
 */
class GiftRuleServiceTest extends TestCase
{
    private const RULE_ID = 12;
    private const RULE_CODE = '12_abc';
    private const PRODUCT_ID = 42;

    private Storage $sessionStorage;
    private Cart|MockObject $cart;
    private GiftRuleHelper $giftRuleHelper;
    private Quote $quote;
    private GiftRuleService $service;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        // Real session and quote data holders, built without their framework dependencies.
        $this->sessionStorage = new Storage('checkout');
        $checkoutSession = (new ReflectionClass(CheckoutSession::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(SessionManager::class, 'storage'))->setValue($checkoutSession, $this->sessionStorage);
        $this->quote = (new ReflectionClass(Quote::class))->newInstanceWithoutConstructor();
        $this->giftRuleHelper = (new ReflectionClass(GiftRuleHelper::class))->newInstanceWithoutConstructor();

        $this->cart = $this->createMock(Cart::class);

        $cacheHelper = $this->createStub(GiftRuleCacheHelper::class);
        $cacheHelper->method('getCachedGiftRule')->willReturn([
            GiftRuleCacheHelper::DATA_PRODUCT_ITEMS => [self::PRODUCT_ID => 'sku'],
            GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT => 1,
            GiftRuleCacheHelper::DATA_PRICE_RANGE => null,
        ]);

        // The constructor references a generated factory: build the service without it.
        $reflection = new ReflectionClass(GiftRuleService::class);
        $this->service = $reflection->newInstanceWithoutConstructor();
        $properties = [
            'checkoutSession' => $checkoutSession,
            'cart' => $this->cart,
            'giftRuleCacheHelper' => $cacheHelper,
            'giftRuleHelper' => $this->giftRuleHelper,
        ];
        foreach ($properties as $property => $value) {
            $reflection->getProperty($property)->setValue($this->service, $value);
        }
    }

    /**
     * A gift rule absent from the checkout session is rejected.
     */
    public function testRuleNotInSessionIsRejected(): void
    {
        $this->givenSessionAndAppliedRules([], (string) self::RULE_ID);

        $this->expectAddRejected();
    }

    /**
     * A gift rule id sent with another rule code is rejected.
     */
    public function testRuleWithAnotherCodeIsRejected(): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => '12_other'], (string) self::RULE_ID);

        $this->expectAddRejected();
    }

    /**
     * A gift rule not applied to the quote is rejected.
     */
    public function testRuleNotAppliedToQuoteIsRejected(): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => self::RULE_CODE], '3,4');

        $this->expectAddRejected();
    }

    /**
     * An applied gift rule adds the gift, flagging it only during the add.
     */
    public function testAppliedRuleAddsGiftWithFlagRaisedOnlyDuringAdd(): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => self::RULE_CODE], '3,' . self::RULE_ID);

        $this->cart->expects($this->once())
            ->method('addProduct')
            ->willReturnCallback(function ($productId, $request) {
                $this->assertSame(self::PRODUCT_ID, $productId);
                $this->assertSame(self::RULE_ID, $request['gift_rule']);
                $this->assertSame(self::RULE_ID, $this->giftRuleHelper->getGiftRuleIdBeingAdded());

                return $this->cart;
            });

        $this->service->addGiftProducts(
            $this->quote,
            [['id' => self::PRODUCT_ID, 'qty' => 1]],
            self::RULE_CODE,
            self::RULE_ID
        );

        $this->assertNull($this->giftRuleHelper->getGiftRuleIdBeingAdded());
    }

    /**
     * Only the keys the gift popup sends reach the buy request: bundle selections and custom options are dropped.
     */
    public function testGiftBuyRequestKeepsOnlyGiftPopupKeys(): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => self::RULE_CODE], (string) self::RULE_ID);

        $this->cart->expects($this->once())
            ->method('addProduct')
            ->willReturnCallback(function ($productId, $request) {
                $this->assertSame(
                    [
                        'id' => self::PRODUCT_ID,
                        'product' => self::PRODUCT_ID,
                        'qty' => 1,
                        'super_attribute' => [93 => 5],
                        'selected_configurable_option' => '43',
                        'gift_rule' => self::RULE_ID,
                    ],
                    $request
                );

                return $this->cart;
            });

        $this->service->addGiftProducts(
            $this->quote,
            [[
                'id' => self::PRODUCT_ID,
                'product' => self::PRODUCT_ID,
                'qty' => 1,
                'super_attribute' => [93 => 5],
                'selected_configurable_option' => '43',
                'bundle_option' => [1 => 2],
                'bundle_option_qty' => [1 => 100],
                'options' => [7 => 'engraving'],
                'gift_rule' => 99,
            ]],
            self::RULE_CODE,
            self::RULE_ID
        );
    }

    /**
     * The remaining number of gifts counts a composite gift by its own qty, not its children's.
     */
    public function testAvailableGiftsCountTopLevelGiftItemsOnly(): void
    {
        $this->sessionStorage->setData('gift_rules', [self::RULE_ID => self::RULE_CODE]);

        $cacheHelper = $this->createStub(GiftRuleCacheHelper::class);
        $cacheHelper->method('getCachedGiftRule')->willReturn([
            'label' => 'Gift',
            'code' => self::RULE_CODE,
            GiftRuleCacheHelper::DATA_PRODUCT_ITEMS => [self::PRODUCT_ID => 'sku'],
            GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT => 3,
            GiftRuleCacheHelper::DATA_PRICE_RANGE => null,
        ]);
        $reflection = new ReflectionClass(GiftRuleService::class);
        $reflection->getProperty('giftRuleCacheHelper')->setValue($this->service, $cacheHelper);
        $reflection->getProperty('giftRuleDataFactory')->setValue($this->service, new class {
            public function create(): GiftRuleData
            {
                return (new ReflectionClass(GiftRuleData::class))->newInstanceWithoutConstructor();
            }
        });

        $parent = $this->createGiftItem(self::PRODUCT_ID, 2);
        $child = $this->createGiftItem(43, 1);
        $child->setParentItem($parent);
        $detachedChild = $this->createGiftItem(44, 1);
        $detachedChild->setData('parent_item_id', 7);
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems'])
            ->getMock();
        $quote->expects($this->once())->method('getAllItems')->willReturn([$parent, $child, $detachedChild]);

        $this->cart->expects($this->never())->method('addProduct');

        $gifts = $this->service->getAvailableGifts($quote);

        $this->assertSame([self::PRODUCT_ID => 2.0], $gifts[self::RULE_ID]->getQuoteItems());
        $this->assertSame(1.0, $gifts[self::RULE_ID]->getRestNumber());
    }

    /**
     * Create a quote item flagged as a gift of the test rule.
     */
    private function createGiftItem(int $productId, float $qty): Item
    {
        $option = (new ReflectionClass(Option::class))->newInstanceWithoutConstructor();
        $option->setData('value', (string) self::RULE_ID);

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptionByCode'])
            ->getMock();
        $item->setData(['product_id' => $productId, 'qty' => $qty]);
        $item->expects($this->atLeastOnce())->method('getOptionByCode')->willReturnCallback(
            fn (string $code) => $code === 'option_gift_rule' ? $option : null
        );

        return $item;
    }

    /**
     * Quantities summed over several products cannot exceed the number of products offered.
     */
    public function testTotalQuantityAboveRuleMaximumIsRejected(): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => self::RULE_CODE], (string) self::RULE_ID);

        $this->expectAddRejected([['id' => self::PRODUCT_ID, 'qty' => 1], ['id' => self::PRODUCT_ID, 'qty' => '1']]);
    }

    /**
     * Quantities must be positive integers.
     */
    #[DataProvider('invalidQtyProvider')]
    public function testInvalidQuantityIsRejected(mixed $qty): void
    {
        $this->givenSessionAndAppliedRules([self::RULE_ID => self::RULE_CODE], (string) self::RULE_ID);

        $this->expectAddRejected([['id' => self::PRODUCT_ID, 'qty' => $qty]]);
    }

    /**
     * Invalid quantities.
     */
    public static function invalidQtyProvider(): array
    {
        return [['-1'], ['0'], ['0.5'], ['1e9'], [['1']]];
    }

    /**
     * Set the session gift rules and the quote applied rule ids.
     */
    private function givenSessionAndAppliedRules(array $sessionGiftRules, string $appliedRuleIds): void
    {
        $this->sessionStorage->setData('gift_rules', $sessionGiftRules);
        $this->quote->setData('applied_rule_ids', $appliedRuleIds);
    }

    /**
     * Assert that adding the gift product is rejected.
     */
    private function expectAddRejected(array $products = [['id' => self::PRODUCT_ID, 'qty' => 1]]): void
    {
        $this->cart->expects($this->never())->method('addProduct');
        $this->expectException(Exception::class);

        $this->service->replaceGiftProducts(
            $this->quote,
            $products,
            self::RULE_CODE,
            self::RULE_ID
        );
    }
}
