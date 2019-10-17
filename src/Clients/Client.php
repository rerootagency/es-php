<?php
namespace RerootAgency\Elasticsearch\Clients;

use Elasticsearch\ClientBuilder;
use RerootAgency\Elasticsearch\Contracts\Client as BaseClient;
use RerootAgency\Elasticsearch\Contracts\ClientInterface;

/**
 * Simple wrapper class around Elasticsearch\Client
 */
class Client extends BaseClient implements ClientInterface
{
    /**
     * Constructor
     *
     * @param string $host
     * @param string|int $port
     * @param string $path
     * @param string $user
     * @param string $pass
     * @param string $scheme
     */
    public function __construct(
        $host = 'localhost',
        $port = 9200,
        $path = '',
        $user = '',
        $pass = '',
        $scheme = 'http'
    ) {
        $conf = [
            'host' => $host,
            'port' => $port,
            'scheme' => $scheme,
        ];

        if(!empty($path)) $conf['path'] = $path;

        if(!empty($user)) {
            $conf['user'] = $user;
            $conf['pass'] = $pass;
        }

        $this->client = ClientBuilder::create()
            ->setHosts([$conf])
            ->build();
    }
}