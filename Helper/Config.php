<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper: Config
 *
 * @copyright 2019 Smile
 */
class Config extends AbstractHelper
{
    /**#@+
     * Config paths.
     */
    public const KEY_CONFIG_AUTOMATIC_ADD = 'smile_gift_sales_rule/configuration/automatic_add';
    public const KEY_CONFIG_AUTOMATIC_POPUP_OPEN = 'smile_gift_sales_rule/configuration/automatic_popup_open';
    /**#@-*/

    /**
     * Get the config value for automatic_add.
     */
    public function isAutomaticAddEnabled(): bool
    {
        $value = (int) $this->scopeConfig->getValue(self::KEY_CONFIG_AUTOMATIC_ADD);

        return ($value == 1);
    }

    /**
     * Get the config value for automatic_popup_open.
     */
    public function isAutomaticPopupOpenEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::KEY_CONFIG_AUTOMATIC_POPUP_OPEN);
    }
}
