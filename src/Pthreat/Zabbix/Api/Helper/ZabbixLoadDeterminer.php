<?php declare(strict_types=1);

namespace Pthreat\Zabbix\Api\Helper;

use Pthreat\Zabbix\Api\Client\Exception\ZabbixApiClientException;
use Pthreat\Zabbix\Api\Client\ZabbixApiClientInterface;

final class ZabbixLoadDeterminer
{
    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';

    /**
     * From a given list of integer host id's, returns a list of hosts sorted by CPU + RAM utilization
     * The sort order is affected by the $sort parameter, which can be one of self::SORT_DESC or self::SORT_ASC
     *
     * @param ZabbixApiClientInterface $client
     * @param int[] $hostIds
     * @param string $sort
     * @return array
     * @throws ZabbixApiClientException
     * @see ZabbixHostHelper::getHostIdentifiersByNameRegex()
     */
    public static function getListSortedByCPUAndRAM(
        ZabbixApiClientInterface $client,
        array $hostIds,
        string $sort = self::SORT_ASC
    ) : array
    {
        $result = $client->get(
            'host.get',
            [
                'output' => [
                    'hostid',
                    'host'
                ],
                'selectInterfaces' => [
                    'interfaceid',
                    'ip',
                ],
                'selectInventory' => true,
                'selectItems' => [
                    'name',
                    'lastvalue',
                    'units',
                    'itemid',
                    'lastclock',
                    'value_type',
                    'itemid'
                ],
                'hostids' => $hostIds,
                'expandDescription' => 1,
                'expandData' => 1
            ]
        );

        $simplify = [];

        array_map(static function($item) use (&$simplify, $sort){
            /**
             * Filter for CPU utilization and Memory Utilization
             */
            $filteredItems = array_filter($item['items'], static function($i){
                $name = strtolower($i['name']);
                return 'cpu utilization' === $name || 'memory utilization' === $name;
            });

            $simplifyFiltered = [];

            array_walk($filteredItems, static function(&$value, $key) use(&$simplifyFiltered){
                $simplifyFiltered[$value['name']] = ceil((float)$value['lastvalue']);
            }, $filteredItems);

            $score = 0;

            array_map(static function($i) use (&$score){
                $score+=$i;
            }, $simplifyFiltered);

            $simplify[$item['host']] = [
                'name' => $item['host'],
                'ip' => $item['interfaces'][0]['ip'],
                'total' => $score,
                'values' => $simplifyFiltered
            ];

        }, $result['result']);

        usort($simplify, static function($a, $b) use ($sort){
            return self::SORT_ASC === $sort ? $a['total'] <=> $b['total'] : $b['total'] <=> $a['total'];
        });

        return $simplify;
    }

    /**
     * From a given list of integer host id's, returns a list of hosts sorted by lesser CPU + RAM utilization
     *
     * @param ZabbixApiClientInterface $client
     * @param int[] $hostIds
     * @see ZabbixHostHelper::getList()
     * @return array
     * @throws ZabbixApiClientException
     */
    public static function getIpAndNameOfMostIdleHosts(
        ZabbixApiClientInterface $client,
        array $hostIds
    ) : array
    {
        $result = self::getListSortedByCPUAndRAM($client, $hostIds, self::SORT_ASC);
        $lesserLoaded = $result[0];

        return [$lesserLoaded['name'] => $lesserLoaded['ip']];
    }

    /**
     * From a given list of integer host id's, returns a list of hosts sorted by biggest CPU + RAM utilization
     *
     * @param ZabbixApiClientInterface $client
     * @param int[] $hostIds
     * @see ZabbixHostHelper::getList()
     * @return array
     * @throws ZabbixApiClientException
     */
    public static function getIpAndNameOfBusiestHosts(
        ZabbixApiClientInterface $client,
        array $hostIds
    ) : array
    {
        $result = self::getListSortedByCPUAndRAM($client, $hostIds, self::SORT_DESC);
        $busiest = $result[0];

        return [$busiest['name'] => $busiest['ip']];
    }

    /**
     * Returns ONLY the ip address of the most idle host
     *
     * @param ZabbixApiClientInterface $client
     * @param int[] $hosts
     * @return string
     * @throws ZabbixApiClientException
     */
    public static function getIpOnlyOfMostIdleHost(ZabbixApiClientInterface $client, array $hosts) : string
    {
        return array_values(self::getIpAndNameOfMostIdleHosts($client, $hosts))[0];
    }

    public static function getIpOnlyOfBusiestHost(ZabbixApiClientInterface $client, array $hosts) : string
    {
        return array_values(self::getIpAndNameOfBusiestHosts($client, $hosts))[0];
    }
}