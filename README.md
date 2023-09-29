# Mage-OS Common Asynchronous Events

Send REST requests to external endpoints asynchronously. This module implements the most common events like order creation and customer change.

This module uses the [Mage-OS Asynchronous Events](https://github.com/mage-os/mageos-async-events/) module as a basis.

## Installation

```
composer require mage-os/mageos-common-async-events
```

If you run into an error like "Could not find a version of package mage-os/mageos-common-async-events matching your minimum-stability (stable).", run this command instead:
```
composer require mage-os/mageos-common-async-events @dev
```

Enable and install the module:
```
bin/magento setup:upgrade
```

## Usage

You can now use the following events without having to implement them yourself.

| Event identifier         | Description                                           |Remarks                  |
|--------------------------|-------------------------------------------------------|-------------------------|
| customer.created         | Whenever a customer is created                        |                  |
| customer.updated         | Whenever a customer is saved, except it's new         |                  |
| customer.address.created | Whenever a customer address is created                |                  |
| customer.address.pdated  | Whenever a customer address is saved, except it's new |                  |
| sales.order.created      | When a new order is created                           |                  |
| sales.order.updated      | When the state of an existing order is changed        ||
| sales.shipment.created   | When a new shipment is created                        |                |
| sales.invoice.created    | When a new invoice is created                         |                 |
| sales.creditmemo.created | When a new creditmemo is created                      |              |

You can use these events by

* [creating a new subscription via Mage-OS REST API](https://github.com/mage-os/mageos-async-events/#create-subscription)
* [creating a new subscription with the Mage-OS Async Events Admin UI module](https://github.com/mage-os/mageos-async-events-admin-ui)
