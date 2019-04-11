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
namespace Smile\GiftSalesRule\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;

/**
 * Controller for processing add to cart action.
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Add constructor.
     *
     * @param Context                  $context
     * @param Validator                $formKeyValidator
     * @param GiftRuleServiceInterface $giftRuleService
     * @param CartRepositoryInterface  $quoteRepository
     * @param Cart                     $cart
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        GiftRuleServiceInterface $giftRuleService,
        CartRepositoryInterface $quoteRepository,
        Cart $cart,
        LoggerInterface $logger
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->giftRuleService = $giftRuleService;
        $this->quoteRepository = $quoteRepository;
        $this->cart = $cart;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $params = $this->getRequest()->getParams();

        if (!isset($params['giftrule']) || !isset($params['product'])) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add this gift item to your shopping cart.')
            );
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $productId    = (int) $params['product'];
        $giftRuleId   = (int) $params['giftrule'];
        $giftRuleCode = $params['giftrulecode'];
        $qty = 1;
        if (isset($params['qty']) && is_int($params['qty'])) {
            $qty = $params['qty'];
        }

        try {
             $this->giftRuleService->addGiftProducts(
                 $this->cart->getQuote(),
                 [['id' => $productId, 'qty' => $qty]],
                 $giftRuleCode,
                 $giftRuleId
             );

            $this->quoteRepository->save($this->cart->getQuote());

            $this->messageManager->addSuccessMessage(
                __('You added gift product to your shopping cart.')
            );
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('We can\'t add this gift item to your shopping cart.')
            );
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
