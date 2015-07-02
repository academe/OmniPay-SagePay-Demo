<?php

// http://acadweb.co.uk/omnipay-demo/authorize.php
// VISA 4929000000006

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

$gateway = OmniPay::create($gateway_server)
    ->setVendor(SagePay::vendor())
    ->setTestMode(true)
    ->setReferrerId('3F7A4119-8671-464F-A091-9E59EB47B80C');

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
    //'returnUrl' => URL::directory() . '/sagepay-confirm.php',
]);

// Process the service request.
// It may involve sending it to the gateway, and it may not.
$responseMessage = $requestMessage->send();

// Store the result.
$transaction = Storage::update($transactionId, [
    'finalStatus' => 'PENDING',
    'status' => $responseMessage->getStatus(),
    'message' => 'Awaiting notify',
    'transactionReference' => $responseMessage->getTransactionReference(),
]);

if ($responseMessage->isSuccessful()) {
    echo "<p><strong>All finished and all successful.</strong></p>";
    $transaction = Storage::update($transactionId, ['finalStatus' => 'APPROVED']);
    echo "<p>The final stored transaction:</p>";
    dump($transaction);

} elseif ($responseMessage->isRedirect()) {
    //dump($responseMessage->getData());
    //echo "<p>Redirecting in ten seconds...<p>";
    //ob_flush(); flush(); sleep(10);
    // OmniPay provides a POST redirect method for convenience.
    // You will probably want to write your own that fits in
    // better with your framework.
    // Some gateways will be happy with a GET redierect, others will
    // need a POST redirect, so be aware of that.
    $responseMessage->redirect();


} else {
    echo "<p>Some kind of error: <strong>" . $responseMessage->getMessage() . "</strong></p>";
    $transaction = Storage::update($transactionId, [
        'finalStatus' => 'ERROR',
        'status' => $responseMessage->getStatus(),
        'message' => $responseMessage->getMessage(),
    ]);
    echo "<p>The final stored transaction:</p>";
    dump($transaction);

}

echo '<p><a href="authorize.php">Try again</a></p>';
