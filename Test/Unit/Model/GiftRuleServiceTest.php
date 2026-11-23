<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Test\Unit\Model;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\Storage;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
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
    private function expectAddRejected(): void
    {
        $this->cart->expects($this->never())->method('addProduct');
        $this->expectException(Exception::class);

        $this->service->replaceGiftProducts(
            $this->quote,
            [['id' => self::PRODUCT_ID, 'qty' => 1]],
            self::RULE_CODE,
            self::RULE_ID
        );
    }
}
