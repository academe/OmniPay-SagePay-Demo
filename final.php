<?php
// Header
include "page.php";

include "vendor/autoload.php";
include "storage.php";

// Remind ourselves what the transaction was.
$transactionId = $_SESSION['transactionId'];

// Go get it.
$transaction = Storage::get($transactionId);

// The results are all in here.
dump($transaction);

echo '<p><a href="authorize.php" class="btn btn-default btn-lg">Try again</a></p>';

// Footer
include "page.php";
