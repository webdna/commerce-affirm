<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="icon"></p>

<h1 align="center">Affirm for Craft Commerce</h1>



## Requirements

This plugin requests Craft 4.x and Craft Commerce 4.x

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Affirm”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require webdna/commerce-affirm

# tell Craft to install the plugin
./craft install/plugin commerce-affirm
```

## Setup

To add an Affirm payment gateway, go to Commerce → Settings → Gateways, create a new gateway.

Enter the Public & Private API keys (environmental variable are recommended) and a product key if you have one.


## Usage

On the payment page, use the getPaymentFormHtml method passing in a few parameters.

```twig
{% set params = {
  cancelUrl: siteUrl('/shop/checkout/payment'),
  confirmationUrl: siteUrl('/shop/checkout/order', {number: cart.number, success:'true'}),
  mode: 'modal',
} %}
{% namespace cart.gateway.handle|commercePaymentFormNamespace %}
	{{ cart.gateway.getPaymentFormHtml(params)|raw }}
{% endnamespace %}
```

### Parameters

`cancelUrl` : the url to return to if the process is cancelled. (only used in 'redirect' mode)

`confirmationUrl` : the url to return to on a successful application. (only used in 'redirect' mode)

`mode` : 'modal' or 'redirect' (default: 'modal')

#### Modal callbacks:

`onFail`, `onSuccess`, `onOpen`, `onValidationError`

if you pass in the `onSuccess` callback, you will need to handle setting the token input with the received `e.checkout_token` and submitting the form.