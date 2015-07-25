<?php
// Header
include "page.php";

include "vendor/autoload.php";
include "storage.php";

// Remind ourselves what the transaction was.
$transactionId = $_SESSION['transactionId'];

// Go get it.
$transaction = Storage::get($transactionId);

if (isset($transaction['finalStatus']) && $transaction['finalStatus'] == 'APPROVED') {
    echo "<h2 class='alert alert-success'><span class='glyphicon glyphicon-ok-sign'></span><strong>All finished and all successful.</strong></h2>";
} else {
    echo "<h2 class='alert alert-danger'><span class='glyphicon glyphicon-remove-sign'></span>Some kind of error: <strong>" . (isset($transaction['message']) ? $transaction['message'] : 'unknown') . "</strong></h2>";
}

// The results are all in here.
dump($transaction);

echo '<p><a href="authorize.php" class="btn btn-default btn-lg">Try again</a></p>';

// Footer
include "page.php";
