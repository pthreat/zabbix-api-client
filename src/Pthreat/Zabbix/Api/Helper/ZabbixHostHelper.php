<?php declare(strict_types=1);

namespace Pthreat\Zabbix\Api\Helper;

use Pthreat\Zabbix\Api\Client\ZabbixApiClientInterface;

final class ZabbixHostHelper
{
    public const ALL_HOSTS = '*';

    public static function getList(
        ZabbixApiClientInterface $client,
        array $output = ['hostid', 'host'],
        array $interfaces=['interfaceid', 'ip']
    ) : array
    {
        return $client->get('host.get',[
            'output' => $output,
            'selectInterfaces' => $interfaces
        ])['result'];
    }

    /**
     * Returns an integer array of host identifiers
     *
     * NOTE: If $regex is equal to * ONLY without any delimiters, it will return all hosts
     *
     * @param string $regex
     * @return int[]
     */
    public static function getHostIdentifiersByNameRegex(ZabbixApiClientInterface $client, string $regex) : array
    {
        $list = array_map(static function ($i) { return $i['hostid']; } , self::getList($client));

        if(self::ALL_HOSTS === $regex){
            return $list;
        }

        return array_values(
            array_filter($list ,static function($item) use ($regex){
                return preg_match($regex,$item['host']) ? $item['hostid'] : false;
            })
        );
    }
}