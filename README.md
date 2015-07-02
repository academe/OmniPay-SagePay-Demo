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

* Check out or clone this repository on your server.
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
