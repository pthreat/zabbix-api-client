<?php declare(strict_types=1);

namespace Pthreat\Zabbix\Api\Client;

use Psr\Http\Message\ResponseInterface;
use Pthreat\Zabbix\Api\Client\Exception\ZabbixApiClientException;

interface ZabbixApiClientInterface
{

    /**
     * @param string $method
     * @param array $parameters
     * @throws ZabbixApiClientException
     * @return ResponseInterface
     */
    public function get(string $method, array $parameters) : array;

    /**
     * @param string $method
     * @param array $parameters
     * @throws ZabbixApiClientException
     *
     * @return ResponseInterface
     */
    public function post(string $method, array $parameters) : array;

    /**
     * @param string $method
     * @param array $parameters
     * @throws ZabbixApiClientException
     *
     * @return ResponseInterface
     */
    public function put(string $method, array $parameters) : array;

    /**
     * @param string $method
     * @param array $parameters
     * @throws ZabbixApiClientException
     *
     * @return ResponseInterface
     */
    public function delete(string $method, array $parameters) : array;
}