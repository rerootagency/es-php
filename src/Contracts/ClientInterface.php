<?php
namespace RerootAgency\Elasticsearch\Contracts;

use Elasticsearch\Client as BaseClient;

interface ClientInterface
{
    /**
     * Elasticsearch base client
     *
     * @var BaseClient
     */
    public function client(): BaseClient;

    /**
     * Build Index
     *
     * @param string $index_class
     * @return array|null
     */
    public function buildIndex(string $index_class): ?array;

    /**
     * Delete index
     *
     * @param string $index_class
     * @return boolean
     */
    public function deleteIndex(string $index_class): bool;

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
    ): array;

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
    ): bool;

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
    ): bool;

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
    ): bool;

    /**
     * Delete record by id (_id)
     *
     * @param string $index_class
     * @param string $id
     * @return bool
     */
    public function delete(string $index_class, string $id): bool;

    /**
     * Set Index for builder
     *
     * @param string $index_class
     * @return ElasticSearch
     */
    public function setIndexClass(string $index_class): ClientInterface;

    /**
     * Where
     *
     * @param string $key
     * @param string $value
     * @param string $operator
     * @return ElasticSearch
     */
    public function where(string $key, string $value, $operator = '='): ClientInterface;

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
    ): ClientInterface;

    /**
     * Limit
     *
     * @param integer $limit
     * @param integer $skip
     * @return ElasticSearch
     */
    public function limit(int $limit, int $skip = 0): ClientInterface;

    /**
     * Sort By
     *
     * @param string $key
     * @param string $sort_order
     * @return ElasticSearch
     */
    public function sortBy(string $key, string $sort_order = 'asc'): ClientInterface;

    /**
     * Search
     *
     * @param Callable|Other $data
     * @return ElasticSearch
     */
    public function search($data): ClientInterface;

    /**
     * Get builder records
     *
     * @return array
     */
    public function get(): array;

    /**
     * Get builder first record
     *
     * @return null|array
     */
    public function first(): ?array;

    /**
     * Find by record id (_id)
     *
     * @param string $index_class
     * @param string $id
     * @return array|null
     */
    public function find(string $index_class, string $id): ?array;

    /**
     * Mass update
     *
     * @param array $data
     * @param boolean $refresh
     * @return boolean
     */
    public function massUpdate(array $data, bool $refresh = true): bool;

    /**
     * Mass delete
     *
     * @param boolean $refresh
     * @return boolean
     */
    public function massDelete($refresh = true): bool;
};