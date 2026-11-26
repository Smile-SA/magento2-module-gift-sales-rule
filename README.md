Gift Sales Rule [![Build Status](https://travis-ci.org/Smile-SA/magento2-module-gift-sales-rule.svg?branch=master)](https://travis-ci.org/Smile-SA/magento2-module-gift-sales-rule)
=====================================

## What is magento2-module-gift-sales-rule Magento 2 Module?

This module provides 2 new actions to Magento cart price rule feature to :
* offer gift product(s) to customer based on native conditions
* offer gift product(s) to customer each time a threshold is reached (every 30$ in cart for example)

For each cart price rule, administrator can define the number of gift products authorized and use a set of conditions to define the gift product list. 
On the frontend side, when the rule is applied, the customer can choose between a list of products the one(s) he prefers and add them to his cart. 

Gift products type can be:
* simple product
* configurable product

## Compatibility

| Module version | Magento | Mage-OS | PHP |
| --- | --- | --- | --- |
| 2.1.x | 2.4.6 and later (tested up to 2.4.9) | 1.0.0 and later (tested up to 4.0) | 8.1 to 8.5, as supported by the platform |
| 1.2.x | 2.4.4 and 2.4.5 | not available | 8.1 |

Earlier releases: 2.0.0 to 2.0.2 require Magento 2.4.7 or later. 1.1.x declared `magento/framework ^102.0.0 || ^103.0.0`, but does not work on Magento 2.4.7 and later, where the signature of `RulesApplier::applyRules()` changed. 2.1.x supports both signatures, and 1.2.x brings the 2.0.x fixes to Magento 2.4.4 and 2.4.5.

## Who is developing this module ? 

![Smile](doc/static/smile.png)

This module is built by Smile. 

Smile is the **European leader of Open Source** and also a four-times **Europe Partner of the the Year** (2010-2014), two-times **Spirit of Excellence** (2015-2016), **Global Elite Partner** (2017-now), and **Regional Commercial Partner** (2018) awarded by Magento.

Our multidisciplinary team brings together experts in technology, innovation, and new applications.

Together we explore, invent, and test technologies of the future, to better serve our clients.

## Main Features

* **Configure cart price rules to select gift products**
![To offer product](doc/static/to_offer_product.png)

* **Configure cart price rules to select gift products and the price range**
![To offer product per price range](doc/static/to_offer_product_per_price_range.png)

* **Display gift product popin**
![Gift popin](doc/static/gift_popin.png)

* **Display added gift product in cart**
![Gift cart](doc/static/gift_cart.png)

## Documentation

Documentation is available [here](https://github.com/Smile-SA/magento2-module-gift-sales-rule/wiki).

## Standards & Code Quality

Built on top of Magento2, our module respects all its prerequisites and code quality rules.

You can also find our PHPCS Standards and PHPMD rulesets in the module directory.

The module is also continuously tested on :

Tools                           | Results
------------------------------- |------------
Travis                          | [![Build Status](https://travis-ci.org/Smile-SA/magento2-module-gift-sales-rule.svg?branch=master)](https://travis-ci.org/Smile-SA/magento2-module-gift-sales-rule)
