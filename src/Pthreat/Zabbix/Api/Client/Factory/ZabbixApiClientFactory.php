<?php declare(strict_types=1);

namespace Pthreat\Zabbix\Api\Client\Factory;

use GuzzleHttp\Client as HttpClient;
use Pthreat\Zabbix\Api\Client\ZabbixApiClient;
use Pthreat\Zabbix\Api\Client\ZabbixApiClientInterface;

final class ZabbixApiClientFactory
{
    public static function getInstance(
        string $url,
        string $zabbixToken,
        string $httpUser = null,
        string $httpPassword = null
    ) : ZabbixApiClientInterface
    {
        return new ZabbixApiClient(
            new HttpClient([
                'base_uri' => $url
            ]),
            $zabbixToken,
            $httpUser,
            $httpPassword
        );
    }
}