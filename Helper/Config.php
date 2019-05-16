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
namespace Smile\GiftSalesRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper: Config
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class Config extends AbstractHelper
{
    /**#@+
     * Config paths.
     */
    const KEY_CONFIG_AUTOMATIC_ADD = 'smile_gift_sales_rule/configuration/automatic_add';
    /**#@-*/

    /**
     * Get the config value for automatic_add.
     *
     * @return bool
     */
    public function isAutomaticAddEnabled()
    {
        $value = (int) $this->scopeConfig->getValue(self::KEY_CONFIG_AUTOMATIC_ADD);

        return ($value == 1);
    }
}
