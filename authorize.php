<?php

// http://acadweb.co.uk/omnipay-demo/authorize.php
// VISA 4929000000006

// Header
include "page.php";

include "vendor/autoload.php";
include "storage.php";

use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;

// SagePay\Direct does the transaction in one go, with no
// redirect involved. SagePay\Server will ask for the user
// to be redirected to the SagePay site.
// Choose the one you want to use
$gateway_server = 'SagePay\Direct';
//$gateway_server = 'SagePay\Server';
//$gateway_server = 'AuthorizeNet_SIM';

// Transaction ID defined by the merchant site.
$transactionId = 'phpne-demo-' . rand(10000000, 99999999);

// Store the transaction ID in the session.
// We will need it after returning from the gateway.
$_SESSION['transactionId'] = $transactionId;

// Define the card details.
// The card and customer are mixed into one object for now.
$card = new CreditCard([
    'firstName' => 'Jason',
    'lastName' => 'Judge',

    // If using SagePay/Server, the credit card details will be
    // collected direct from the customer, so remove these four entries.
    'number' => '4929000000006',
    'expiryMonth' => '12',
    'expiryYear' => '2016',
    'CVV' => '123',

    'billingAddress1' => 'Campus North',
    'billingAddress2' => '5 Carliol Square',
    'billingState' => null,
    'billingCity' => 'Newcastle Upon Tyne',
    'billingPostcode' => 'NE1',
    'billingCountry' => 'GB',

    'shippingAddress1' => 'Campus North',
    'shippingAddress2' => '5 Carliol Square',
    'billingState' => null,
    'shippingCity' => 'Newcastle Upon Tyne',
    'shippingPostcode' => 'NE1',
    'shippingCountry' => 'GB',
]);

// Create the gateway.
// You will need your own test vendor name and also will
// need to give SagePay your server's IP address.
// We will run the API in test mode, which automatically
// switches to the test endpoint URLs.

if ($gateway_server == 'SagePay\Direct' || $gateway_server == 'SagePay\Server') {
    $gateway = OmniPay::create($gateway_server)
        ->setVendor(getenv('VENDOR'))
        ->setTestMode(true)
        ->setReferrerId('3F7A4119-8671-464F-A091-9E59EB47B80C');
} elseif ($gateway_server == 'AuthorizeNet_SIM' || $gateway_server == 'AuthorizeNet_DPM') {
    $gateway = OmniPay::create($gateway_server)
        ->setApiLoginId(getenv('API_LOGIN_ID'))
        ->setTransactionKey(getenv('TRANSACTION_KEY'))
        ->setHashSecret(getenv('HASH_SECRET'))
        ->setTestMode(true)
        ->setDeveloperMode(true);
}

// Get the message for the service we want - purchase in this case.
$requestMessage = $gateway->purchase([
    'amount' => '99.99',
    'currency' => 'GBP',
    'card' => $card,
    'transactionId' => $transactionId,
    'description' => 'Pizzas for everyone',

    // No return URL is needed for SagePay\Direct.
    // It will be needed for SagePay\Server - try it with and without
    // to see what happens.
    'returnUrl' => URL::directory() . '/sagepay-confirm.php',

    // A notify URL is needed for Authorize.Net
    'notifyUrl' => URL::directory() . '/authorizenet-confirm.php',
]);

// Process the service request.
// It may involve sending it to the gateway, and it may not.
$responseMessage = $requestMessage->send();

// Store the result.
// Note here that SagePay has getStatus() while Authorize.Net has getCode(). These all need
// to be nornalised.
$transaction = Storage::update($transactionId, [
    'finalStatus' => 'PENDING',
    'status' => method_exists($responseMessage, 'getStatus') ? $responseMessage->getStatus() : $responseMessage->getCode(),
    'message' => 'Awaiting notify',
    'transactionReference' => $responseMessage->getTransactionReference(),
]);

if ($responseMessage->isSuccessful()) {
    echo "<h2 class='alert alert-success'><span class='glyphicon glyphicon-ok-sign'></span><strong>All finished and all successful.</strong></h2>";
    $transaction = Storage::update($transactionId, ['finalStatus' => 'APPROVED']);
    echo "<p>The final stored transaction:</p>";
    dump($transaction);

} elseif ($responseMessage->isRedirect()) {
    // OmniPay provides a POST redirect method for convenience.
    // You will probably want to write your own that fits in
    // better with your framework.
    // Some gateways will be happy with a GET redierect, others will
    // need a POST redirect, so be aware of that.
    $responseMessage->redirect();


} else {
    echo "<h2 class='alert alert-danger'><span class='glyphicon glyphicon-remove-sign'></span>Some kind of error: <strong>" . $responseMessage->getMessage() . "</strong></h2>";
    $transaction = Storage::update($transactionId, [
        'finalStatus' => 'ERROR',
        'status' => $responseMessage->getStatus(),
        'message' => $responseMessage->getMessage(),
    ]);
    echo "<p>The final stored transaction:</p>";
    dump($transaction);

}

echo '<p><a href="authorize.php" class="btn btn-default btn-lg">Try again</a></p>';

// Footer
include "page.php";
