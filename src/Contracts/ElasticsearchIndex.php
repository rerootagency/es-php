<?php
namespace RerootAgency\Elasticsearch\Contracts;

abstract class ElasticsearchIndex
{
    public static abstract function indexName(): string;

    public static function esPropMappings(): array
    {
        return [];
    }

    public static function settings(): array
    {
        return [];
    }

    public static function ngramSettings(): array
    {
        return [
            'index.max_ngram_diff' => 48,
            'analysis' => [
                'analyzer' => [
                    'ngram_analyzer' => [
                        'tokenizer' => 'ngram_tokenizer'
                    ],
                ],
                'tokenizer' => [
                    'ngram_tokenizer' => [
                        'type' => 'ngram',
                        'min_gram' => 2,
                        'max_gram' => 50,
                        'token_chars' => [
                            'letter',
                            'digit',
                            'punctuation',
                            'symbol'
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function searchBody($body, $data): array
    {
        return [];
    }
}