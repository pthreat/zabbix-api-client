<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Pthreat\Zabbix\Api\Client\Factory\ZabbixApiClientFactory;
use Pthreat\Zabbix\Api\Helper\ZabbixHostHelper;
use Pthreat\Zabbix\Api\Helper\ZabbixLoadDeterminer;
use Pthreat\Zabbix\Api\Client\Exception\ZabbixApiClientException;

/**
 * This is the url of your Zabbix frontend, needless to say, this URL MUST BE HTTPS for your own safety.
 */
define('ZABBIX_URL', 'https://your-zabbix-server/api_jsonrpc.php');

/**
 * Create this token from your Zabbix Frontend UI and make it permanent,
 */
define('ZABBIX_TOKEN', '');

/**
 * If your frontend is protected with additional HTTP BASIC AUTH define these two here
 * if it's not, comment out ZABBIX_HTTP_USER and ZABBIX_HTTP_PASSWORD in the factory method call bellow
 */
define('ZABBIX_HTTP_USER', 'http-user');
define('ZABBIX_HTTP_PASS', 'http-password');

$client = ZabbixApiClientFactory::getInstance(
    'https://your-zabbix-server/api_jsonrpc.php',
    ZABBIX_TOKEN,
    ZABBIX_HTTP_USER,
    ZABBIX_HTTP_PASS
);

try {
    /**
     * Obtain all available hosts, note that the second argument can be a regex, with a proper naming scheme
     * in your zabbix configuration this becomes trivial to get a group of hosts matching certain names.
     */
    $hosts = ZabbixHostHelper::getHostIdentifiersByNameRegex($client, ZabbixHostHelper::ALL_HOSTS);

    /**
     * Determine which hosts have the highest RAM + CPU usage, this is useful when you want to, for example, load balance
     * some jobs through a work queue in different servers
     */
    $mostIdleList = ZabbixLoadDeterminer::getIpAndNameOfMostIdleHosts($client, $hosts);

    var_dump($mostIdleList);

    /**
     * Same as above, except only ONE IP address will be returned
     */
    $mostIdle = ZabbixLoadDeterminer::getIpOnlyOfMostIdleHost($client, $hosts);

}catch(ZabbixApiClientException $e){

    echo "[ERROR]: {$e->getMessage()}\n";

}