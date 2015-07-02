# OmniPay-SagePay-Demo
Simple SagePay demo scripts

This demo was used at a short talk to PHP North East (PHPNE) in the UK, in June 2015.

The slides are here: https://github.com/academe/OmniPay-SagePay-Demo
and also can be viewed here: http://slidedeck.io/academe/OmniPay-PHPNE-talk

The demo shows how to make a simple authorisation with SagePay Direct, and then how to
change that to SagePay Server with just a few lines of code changed.

## Pre-requisites

It needs to be installed on a web server that can receive incoming requests,
as SagePay Server will need to call your `sagepay-confirm.php` script.
You will also need a SagePay test account, and to register your server's IP address
with that account. There are no further passwords that need to be configured.

Youy will also need a MySQL database. You could use any relational database if you change
the PDO connection details in `storage.php`, but we'll stick with MySQL as it was handy
for the demo.

## Installation

* Check out or clone this repository on your server. The demo will work in a directory, so does not need to go at the root of a domain.
* Make sure you have composer available, and install the dependencies: `composer install` or `php composer.phar install`
* Create a database with a single table, as detaied below.
* Copy the `.env.sample` file to `.env` and edit it. Enter the database connection detgails and your SagePay vendor name.


The database details are:

~~~sql
CREATE TABLE IF NOT EXISTS `transactions` (
`id` varchar(100) NOT NULL,
`data` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
~~~

It's a very simple storage. We stick JSON into it and treat it like a name/value pair database.

That should be it. Let me know if I've left anything out.

## Running the Demo

To authorise a payment go to: `http://example.co.uk/omnipay-demo/authorize.php` with your domain and path replacing the two shown. If all runs well, you should get an instant and successful authorisation. The data collected for the transaction will be dumped to the page so you can see it. That is a SagePay Direct authorisation.

To switch to SagePay Server, a few changes need to be made:

* In authorize.php change `$gateway_server = 'SagePay\Direct';` to `$gateway_server = 'SagePay\Server';`
* Comment out the credit card details from the `new CreditCar([...])` section.
* Give the transaction a notification handler URL. This is set as the "returnUrl" in `authorization.php`, which is commented out to start with.

Now try it again. This time you should be redirected to the SagePay site to enter your credit card details and
to confirm your address. A callback to `sagepay-confirm.php` will be made with the results, and you will
ultimately be sent on to `final.php`.

If that works, then you can try various failure modes to see how it works. Try cancelling, entering the wrong CC
multiple times, remove vital parts of the address in `authorize.php` and see how those errors are all handled.

Let me know how it goes. It should be really easy to set up and run, but I may have missed out an important
step or two that you can reveal for me.
