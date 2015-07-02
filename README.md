# OmniPay-SagePay-Demo
Simple SagePay demo scripts

This demo was used at a short talk to PHP North East (PHPNE) in the UK, in June 2015.

The slides are here: https://github.com/academe/OmniPay-SagePay-Demo
and also can be viewed here: http://slidedeck.io/academe/OmniPay-PHPNE-talk

The demo shows how to make a simple authorisation with SagePay Direct, and then how to
change that to SagePay Server with just a few lines of code changed.

It needs to be installed on a web server that can receive incoming requests,
as SagePay Server will need to call your `sagepay-confirm.php` script.
You will also need a SagePay test account, and to register your server's IP address
with that account. There are no further passwords that need to be configured.

