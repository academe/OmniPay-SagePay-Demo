<?php

// Do some common stuff, such as handle storage and introduce some
// nicer error reporting.
// I know, not PSR standard by any means.

session_start();

// Set up HTML formatted exception handler.
$runner = new League\BooBoo\Runner();
$runner->pushFormatter(new League\BooBoo\Formatter\HtmlTableFormatter());
$runner->register();

// Register dotenv for config data
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->overload();

// Get the current base URL.
class URL
{
    public static function directory()
    {
        return "http"
            . (!empty($_SERVER['HTTPS']) ? "s" : "")
            . "://"
            . $_SERVER['SERVER_NAME']
                . dirname($_SERVER['REQUEST_URI']);
    }
}

/**
 * Table 'transactions' details:
 *
 * CREATE TABLE IF NOT EXISTS `transactions` (
 *  `id` varchar(100) NOT NULL,
 *  `data` text NOT NULL,
 *  PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*
* This storage just implements a simple key/value store.
* It's dead simple, just for the demo, but a live site
* probably does not need much more than this.
 */

class Storage {
    protected static $host = 'localhost';
    protected static $table = 'transactions';
    // Columns(id and data)

    public static function connect()
    {
        static $connection;

        if (empty($connection)) {
            $connection = new PDO(
                "mysql:host=" . static::$host . ";dbname=" . getenv('DATABASE') . "",
                getenv('USER'),
                getenv('PASSWORD')
            );

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $connection;
    }

    // $data is an array of name/values to merge in.
    public static function update($id, $data)
    {
        $old_data = static::get($id);
        $new_data = array_merge($old_data, $data);

        $db = static::connect();

        $sql = "REPLACE `" . static::$table . "` (id, data) VALUES (:id, :data)";

        $statement = $db->prepare($sql);

        $statement->execute([':id' => $id, ':data' => json_encode($new_data)]);

        return $new_data;
    }

    public static function get($id)
    {
        $db = static::connect();

        $sql = "SELECT `data` FROM `" . static::$table . "` WHERE id = :id";

        $statement = $db->prepare($sql);

        $statement->execute([':id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['data'])) {
            $expanded = @json_decode($row['data'], true);
            if (empty($expanded)) {
                $expanded = [];
            }
        } else {
            $expanded = [];
        }

        return $expanded;
    }
}
