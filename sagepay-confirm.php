<?php

include "vendor/autoload.php";
include "storage.php";

use Omnipay\Omnipay;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\SagePay\Message\ServerCompleteAuthorizeResponse;

// Where we want SagePay to return the user to.
$finalUrl = URL::directory() . '/final.php';

// Get the index ID for the transaction.
// This should be a static method of the OmniPay driver IMO.
$transactionId = $_POST['VendorTxCode'];

// Retrieve the transaction from the database.
$transaction = Storage::get($transactionId);

// If we can't find the transaction, or it is in the wrong status,
// then bail out now.
// FIXME: I think instead of bailing out like this, we could return 
// a proper response. Perhaps just make sure we pass in a blank
// transactionReference before doing the send() and that should
// catch the post as invalid.
if (empty($transaction) || $transaction['finalStatus'] != 'PENDING') {
    exit("vendorTxCode missing or invalid - aborting");
}

// Get the gateway driver.
// Don't forget to use your own vendor name.
// Always "Server". "Direct" will never get here.
$gateway = OmniPay::create('SagePay\Server')
    ->setVendor(SagePay::vendor())
    ->setTestMode(true);

// Get the "complete purchase" message.
$requestMessage = $gateway->completePurchase([
    'transactionId' => $transactionId, // CHECKME: do we need to pass this in? If so, why? It's in POST data.
    'transactionReference' => $transaction['transactionReference'],
]);

// Do a "send" - this will validate everything.
try {
    $responseMessage = $requestMessage->send();
} catch(InvalidResponseException $e) {
    // This is a nasty hack, manually creating a message in the
    // event of an exception caused by a security failure.
    $requestMessage = $gateway->completePurchase([]);
    $responseMessage = new ServerCompleteAuthorizeResponse($requestMessage, []);
    $responseMessage->invalid($finalUrl, $e->getMessage());
}

// Handle the actions based on successful (or not) authorisation
// of the transaction.
if ($responseMessage->isSuccessful()) {
    $finalStatus = 'APPROVED';
    // Set the ball rolling here with processing the transaction.
    // Perhaps throw the result in a queue.

} else {
    $finalStatus = 'REJECTED';
}

// Store the result.
Storage::update($transactionId, [
    'finalStatus' => $finalStatus,
    'status' => $responseMessage->getStatus(),
    'message' => $responseMessage->getMessage(),
    'notifyData' => $responseMessage->getData(),
]);

// Return to SagePay with a big, fat "got it, thanks" message.
$responseMessage->confirm($finalUrl);
