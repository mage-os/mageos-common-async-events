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

| Event identifier         | Description                                           | Remarks |
|--------------------------|-------------------------------------------------------|---------|
| customer.created         | Whenever a customer is created                        |         |
| customer.updated         | Whenever a customer is saved, except it's new         |         |
| customer.login           | Whenever a customer logs in successfully              |         |
| customer.address.created | Whenever a customer address is created                |         |
| customer.address.updated | Whenever a customer address is saved, except it's new |         |
| sales.order.created      | When a new order is created                           |         |
| sales.order.updated      | When the state of an existing order is changed        |         |
| sales.order.paid         | When an order is fully paid                           |         |
| sales.order.shipped      | When an order is fully shipped                        |         |
| sales.order.holded       | When an order is set "on hold"                        |         |
| sales.order.unholded     | When an order is released from "on hold"              |         |
| sales.order.cancelled    | When an order is cancelled                            |         |
| sales.shipment.created   | When a new shipment is created                        |         |
| sales.invoice.created    | When a new invoice is created                         |         |
| sales.invoice.paid       | When an invoice is paid                               |         |
| sales.creditmemo.created | When a new creditmemo is created                      |         |
| catalog.product.created  | When a new product is created                         |         |
| catalog.product.updated  | When a product is updated                             |         |

You can use these events by

* [creating a new subscription via Mage-OS REST API](https://github.com/mage-os/mageos-async-events/#create-subscription)
* [creating a new subscription with the Mage-OS Async Events Admin UI module](https://github.com/mage-os/mageos-async-events-admin-ui)


## 3rd Party Events

| Event identifier | Description | Module                                                                                                                           |
|-|-|----------------------------------------------------------------------------------------------------------------------------------|
| customer.login_failed | Whenever a customer fails to log in | [`zero1/async-event-customer-login-failed`](https://github.com/zero1limited/magento2-module-async-event-customer-login-failed)   |
| customer.create_failed | Whenever a customer fails to sign up for an account | [`zero1/async-event-customer-create-failed`](https://github.com/zero1limited/magento2-module-async-event-customer-create-failed) |
