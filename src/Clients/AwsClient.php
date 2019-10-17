<?php
namespace RerootAgency\Elasticsearch\Clients;

use RerootAgency\Elasticsearch\Contracts\Client as BaseClient;
use RerootAgency\Elasticsearch\Contracts\ClientInterface;

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
    ) {
    }
}