<?php
namespace RerootAgency\Elasticsearch\Contracts;

abstract class Client
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

    /**
     * Elasticsearch base client
     *
     * @var mixed
     */
    protected $client;

    /**
     * Expose underlying client
     *
     * @return mixed
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Check if $index_class is Instance of ElasticsearchIndex abstract class
     *
     * @param string $index_class
     * @return void
     */
    protected function check(string $index_class)
    {
        $reflection = new \ReflectionClass($index_class);

        if($reflection->isSubclassOf(ElasticsearchIndex::class)) return;

        throw new \Exception('Class '.$index_class.' class must be a subclass off '.ElasticsearchIndex::class.'.');
    }

    /**
     * Build Index
     *
     * @param string $index_class
     * @return array|null
     */
    public function buildIndex(string $index_class): ?array
    {
        $data = [
            'index' => $index_class::indexName(),
        ];

        if($this->client->indices()->exists($data)) return null;

        $mappings = $index_class::esPropMappings();
        $settings = $index_class::settings();

        if(!empty($mappings)) {
            $data['body']['mappings']['properties'] = $mappings;
        }
        if(!empty($settings)) {
            $data['body']['settings'] = $settings;
        }

        return $this->client->indices()->create($data);
    }

    /**
     * Delete index
     *
     * @param string $index_class
     * @return boolean
     */
    public function deleteIndex(string $index_class): bool
    {
        $this->check($index_class);

        try {
            $this->client->indices()->delete([
                'index' => $index_class::indexName(),
            ]);
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * Create Index entry
     *
     * @param string $index_class
     * @param array $body
     * @param boolean $refresh
     * @return array
     */
    public function index(
        string $index_class,
        array $body,
        bool $refresh = true
    ): array {
        $this->check($index_class);

        $this->buildIndex($index_class);

        $data = [
            'index' => $index_class::indexName(),
            'body' => $body,
        ];

        if($refresh) {
            $data['refresh'] = 'wait_for';
        }

        return $this->client->index($data);
    }

    /**
     * Bulk insert entries
     *
     * @param string $index_class
     * @param array $data
     * @param boolean $refresh
     * @return boolean
     */
    public function bulkInsert(
        string $index_class,
        array $data,
        bool $refresh = true
    ): bool {
        $this->check($index_class);

        $this->buildIndex($index_class);

        $index = [
            'index' => [
                '_index' => $index_class::indexName(),
            ]
        ];

        $payload = [
            'body' => [],
        ];

        if($refresh) {
            $payload['refresh'] = 'wait_for';
        }

        $data_count = count($data);

        for ($i = 0; $i < $data_count; $i++) {
            $payload['body'][] = $index;
            $payload['body'][] = $data[$i];

            if(($i + 1) % 1000 == 0) {
                $this->client->bulk($payload);

                $payload['body'] = [];
            }
        }

        if (!empty($payload['body'])) {
            $this->client->bulk($payload);
        }

        return true;
    }

    /**
     * Update document
     *
     * @param string $index_class
     * @param string $id
     * @param array $data
     * @param boolean $refresh
     * @return boolean
     */
    public function update(
        string $index_class,
        string $id,
        array $data,
        bool $refresh = true
    ): bool {
        $this->check($index_class);

        $payload = [
            'index' => $index_class::indexName(),
            'id' => $id,
            'body' => []
        ];

        $script = "";

        foreach ($data as $key => $value) {
            if(is_string($value)) {
                $value = "'".$value."'";
            }

            $script .= "ctx._source.{$key}= {$value};";
        }

        if($refresh) {
            $payload['refresh'] = 'wait_for';
        }

        $payload['body']['script'] = [
            'source' => $script,
            "lang" => 'painless',
        ];

        try {
            $this->client->update($payload);
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * UpdateOrCreate
     *
     * @param string $index_class
     * @param array $terms
     * @param array $data
     * @param boolean $refresh
     * @return boolean
     */
    public function updateOrCreate(
        string $index_class,
        array $terms,
        array $data,
        bool $refresh = true
    ): bool {
        $this->check($index_class);

        $this->buildIndex($index_class);

        $this->setIndexClass($index_class);

        foreach ($terms as $key => $value) {
            $this->where($key, $value);
        }

        $result = $this->first();

        $this->body = [];
        $this->index_class = null;

        try {
            if($result) {
                $payload = [
                    'index' => $index_class::indexName(),
                    'id' => $result['_id'],
                    'body' => []
                ];

                $script = "";

                foreach ($data as $key => $value) {
                    if(is_string($value)) {
                        $value = "'".$value."'";
                    }

                    $script .= "ctx._source.{$key}= {$value};";
                }

                if($refresh) {
                    $payload['refresh'] = 'wait_for';
                }

                $payload['body']['script'] = [
                    'source' => $script,
                    "lang" => 'painless',
                ];

                $this->client->update($payload);
            } else {
                $payload = [
                    'index' => $index_class::indexName(),
                    'body' => array_merge($terms, $data),
                ];

                if($refresh) {
                    $payload['refresh'] = 'wait_for';
                }

                $this->client->index($payload);
            }
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * Delete record by id (_id)
     *
     * @param string $index_class
     * @param string $id
     * @return bool
     */
    public function delete(string $index_class, string $id): bool
    {
        try {
            $this->client->delete([
                'index' => $index_class::indexName(),
                'id' => $id
            ]);
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * Set Index for builder
     *
     * @param string $index_class
     * @return ElasticSearch
     */
    public function setIndexClass(string $index_class): ClientInterface
    {
        $this->check($index_class);

        $this->index_class = $index_class;

        return $this;
    }

    /**
     * Where
     *
     * @param string $key
     * @param string $value
     * @param string $operator
     * @return ElasticSearch
     */
    public function where(string $key, string $value, $operator = '='): ClientInterface
    {
        switch ($operator) {
            case '=':

                $this->body['query']['bool']['must'][] = [
                    'term' => [
                        $key => $value
                    ]
                ];

                break;
            case '!=':

                $this->body['query']['bool']['must_not'][] = [
                    'term' => [
                        $key => $value
                    ]
                ];

                break;
            case '>':

                $this->body['query']['bool']['must'][] = [
                    'range' => [
                        $key => ['gt' => $value]
                    ]
                ];

                break;
            case '<':

                $this->body['query']['bool']['must'][] = [
                    'range' => [
                        $key => ['lt' => $value]
                    ]
                ];

                break;
            case '>=':

                $this->body['query']['bool']['must'][] = [
                    'range' => [
                        $key => ['gte' => $value]
                    ]
                ];

                break;
            case '<=':

                $this->body['query']['bool']['must'][] = [
                    'range' => [
                        $key => ['lte' => $value]
                    ]
                ];

                break;

            default:
                throw new \Exception('Unsupported operator.');
        }

        return $this;
    }

    /**
     * Where In
     *
     * @param string $key
     * @param array $values
     * @param boolean $match_values
     * @return ElasticSearch
     */
    public function whereIn(
        string $key,
        array $values,
        bool $match_values = true
    ): ClientInterface {
        if($match_values) {
            $this->body['bool']['must'][] = [
                'terms' => [
                    $key => $values
                ]
            ];
        } else {
            $this->body['bool']['must_not'][] = [
                'terms' => [
                    $key => $values
                ]
            ];
        }

        return $this;
    }

    /**
     * Limit
     *
     * @param integer $limit
     * @param integer $skip
     * @return ElasticSearch
     */
    public function limit(int $limit, int $skip = 0): ClientInterface
    {
        $this->body['from'] = $skip;
        $this->body['size'] = $limit;


        return $this;
    }

    /**
     * Sort By
     *
     * @param string $key
     * @param string $sort_order
     * @return ElasticSearch
     */
    public function sortBy(string $key, string $sort_order = 'asc'): ClientInterface
    {
        $sort_order = strtolower($sort_order);

        if(in_array($sort_order, ['asc', 'desc'])) {
            $sort_order = 'asc';
        }

        $this->body['sort'] = [
            [
                $key => $sort_order
            ]
        ];

        return $this;
    }

    /**
     * Search
     *
     * @param Callable|Other $data
     * @return ElasticSearch
     */
    public function search($data): ClientInterface
    {
        if(is_callable($data)) {
            $this->body = $data($this->body);
        } else {
            $this->body = $this->index_class::searchBody($this->body, $data);
        }

        return $this;
    }

    /**
     * Get builder records
     *
     * @return array
     */
    public function get(): array
    {
        if(!$this->index_class) {

            throw new \Exception('Index was not set.');
        }

        if(empty($this->body['sort'])) {

            $this->body['sort'] = [
                '_score'
            ];
        }

        $result = $this->client->search([
            'index' => $this->index_class::indexName(),
            'body' => $this->body,
        ]);

        $this->body = [];
        $this->index_class = null;

        if(empty($result['hits']['hits'])) return [];

        return array_map(function($item) {
            $out = $item['_source'];
            $out['_id'] = $item['_id'];

            return $out;
        },$result['hits']['hits']);
    }

    /**
     * Get builder first record
     *
     * @return null|array
     */
    public function first(): ?array
    {
        $this->limit(1);

        $result = $this->get();

        return array_pop($result);
    }

    /**
     * Find by record id (_id)
     *
     * @param string $index_class
     * @param string $id
     * @return array|null
     */
    public function find(string $index_class, string $id): ?array
    {
        $this->check($index_class);

        try {
            $result = $this->client->get([
                'index' => $index_class::indexName(),
                'id' => $id,
            ]);
        } catch (\Exception $e) {

            return null;
        }

        $out = $result['_source'];
        $out['id'] = $result['id'];

        return $out;
    }

    /**
     * Mass update
     *
     * @param array $data
     * @param boolean $refresh
     * @return boolean
     */
    public function massUpdate(array $data, bool $refresh = true): bool
    {
        if(!$this->index_class) {

            throw new \Exception('Index was not set.');
        }

        $payload = [
            'index' => $this->index_class::indexName(),
            'refresh' => $refresh,
            'body' => $this->body,
        ];

        $script = "";

        foreach ($data as $key => $value) {
            if(is_string($value)) {
                $value = "'".$value."'";
            }

            $script .= "ctx._source.{$key}= {$value};";
        }

        if(empty($script)) {
            throw new \Exception('Invalid update payload.');
        }

        $payload['body']['script'] = [
            'source' => $script,
            "lang" => 'painless',
        ];

        $this->body = [];
        $this->index_class = null;

        try {
            $this->client->updateByQuery($payload);
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

    /**
     * Mass delete
     *
     * @param boolean $refresh
     * @return boolean
     */
    public function massDelete($refresh = true): bool
    {
        if(!$this->index_class) {

            throw new \Exception('Index was not set.');
        }

        $payload = [
            'index' => $this->index_class::indexName(),
            'refresh' => $refresh,
            'body' => $this->body,
        ];

        $this->body = [];
        $this->index_class = null;

        try {
            $this->client->deleteByQuery($payload);
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }
}