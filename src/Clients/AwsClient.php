<?php
namespace RerootAgency\Elasticsearch\Clients;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Aws\Signature\SignatureV4;
use RerootAgency\Elasticsearch\Contracts\Client as BaseClient;
use RerootAgency\Elasticsearch\Contracts\ClientInterface;
use Elasticsearch\ClientBuilder;

/**
 * Simple wrapper class around Aws Elasticsearch Client
 */
class AwsClient extends BaseClient implements ClientInterface
{
    /**
     * Index parameter
     *
     * @var string
     */
    protected $index_class;

    /**
     * Body parameter
     *
     * @var array
     */
    protected $body = [];

    protected $client;

    public function __construct(
        $aws_key,
        $aws_secret,
        $aws_region,
        $host,
        $port,
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

        $provider = CredentialProvider::fromCredentials(
            new Credentials($aws_key, $aws_secret)
        );

        $handler = new ElasticsearchPhpHandler($aws_region, $provider);

        $this->client = ClientBuilder::create()
            ->setHandler($handler)
            ->setHosts([$conf])
            ->build();
    }
}