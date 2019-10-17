<?php
namespace RerootAgency\Elasticsearch\Clients;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use RerootAgency\Elasticsearch\Contracts\Client as BaseClient;
use RerootAgency\Elasticsearch\Contracts\ClientInterface;
use Elasticsearch\ClientBuilder;
use Wizacha\Middleware\AwsSignatureMiddleware;

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
        $aws_id,
        $aws_secret,
        $aws_region,
        $host,
        $port,
        $path = '',
        $user = '',
        $pass = '',
        $scheme = 'http'
    ) {
        $credentials = new Credentials($aws_id, $aws_secret);
        $signature = new SignatureV4('es', $aws_region);

        $middleware = new AwsSignatureMiddleware($credentials, $signature);
        $defaultHandler = ClientBuilder::defaultHandler();
        $awsHandler = $middleware($defaultHandler);

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

        return ClientBuilder::create()
            ->setHandler($awsHandler)
            ->setHosts([$conf])
            ->build();
    }
}