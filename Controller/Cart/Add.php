<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Controller\Cart;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;

/**
 * Controller for processing add to cart action.
 *
 * @copyright 2019 Smile
 */
class Add extends Action implements HttpPostActionInterface
{
    /**
     * Add constructor.
     *
     * @param Context                  $context          Context
     * @param Validator                $formKeyValidator Form key validator
     * @param GiftRuleServiceInterface $giftRuleService  Gift rule service
     * @param CartRepositoryInterface  $quoteRepository  Quote repository
     * @param Cart                     $cart             Cart
     * @param LoggerInterface          $logger           Logger
     */
    public function __construct(
        Context $context,
        protected Validator $formKeyValidator,
        protected GiftRuleServiceInterface $giftRuleService,
        protected CartRepositoryInterface $quoteRepository,
        protected Cart $cart,
        protected LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Add product to shopping cart action
     */
    public function execute(): Redirect
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );

            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $params = $this->getRequest()->getParams();

        if (!$this->validatePostParameters($params)) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add this gift item to your shopping cart.')
            );

            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $productData = $this->formatProductPostParameters($params);

        try {
             $this->giftRuleService->replaceGiftProducts(
                 $this->cart->getQuote(),
                 $productData,
                 (string) $params['gift_rule_code'],
                 (int) $params['gift_rule_id']
             );

            $this->quoteRepository->save($this->cart->getQuote());

            $this->messageManager->addSuccessMessage(
                __('You added gift product to your shopping cart.')
            );

            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('We can\'t add this gift item to your shopping cart.')
            );

            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }

    /**
     * Validate post parameters.
     *
     * @param array $params params
     */
    protected function validatePostParameters(array $params): bool
    {
        $return = true;

        if (
            !isset($params['gift_rule_code'])
            || !isset($params['gift_rule_id'])
            || !isset($params['products'])
            || !is_array($params['products'])
        ) {
            $return = false;
        }

        if ($return) {
            foreach ($params['products'] as $productData) {
                if (!isset($productData['qty'])) {
                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Format post parameters for the add to cart method.
     *
     * @param array $params params
     */
    protected function formatProductPostParameters(array $params): array
    {
        $filteredParams = [];
        foreach ($params['products'] as $productId => $productData) {
            if ($productData['qty']) {
                $productData['uenc'] = $params['uenc'];
                $productData['id'] = $productId;
                $productData['product'] = $productId;
                $productData['item'] = $productId;
                $filteredParams[] = $productData;
            }
        }

        return $filteredParams;
    }
}
