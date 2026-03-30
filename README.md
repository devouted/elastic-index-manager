# Elastic Index Manager

A Symfony console tool for managing Elasticsearch indexes interactively.

## Requirements

- PHP 8.2+
- Symfony 7.3+
- Elasticsearch PHP client 8.x

## Installation

```bash
composer require devouted/elastic-index-manager
```

## Configuration

Register the command in your `services.yaml`:

```yaml
Devouted\ElasticIndexManager\Command\ElasticIndexManagerCommand:
    tags: ['console.command']
```

The command automatically discovers any service returning an **Elastic\Elasticsearch\Client** or **Elastic\Elasticsearch\ClientBuilder** instance. Example service definition:

```yaml
elasticsearch.client.default:
    public: true
    class: Elastic\Elasticsearch\ClientBuilder
    factory: [ 'Elastic\Elasticsearch\ClientBuilder', 'create' ]
    calls:
        - [ setHosts, [ [ '%env(ELASTICSEARCH_TRANSPORT)%://%env(ELASTICSEARCH_HOST)%:%env(ELASTICSEARCH_PORT)%' ] ] ]
```

## Usage

```bash
bin/console elasticsearch:index:manage
```

### Connection selection

Choose which Elasticsearch connection to use from discovered services.

![image](https://github.com/user-attachments/assets/4c6bb436-bd08-4800-9bbc-d752f599d08a)

### Index management

Once connected, you get an interactive table of all indexes with a summary footer showing totals for document counts and storage sizes.

![image2](https://github.com/user-attachments/assets/de3cdc2a-765d-4d22-9c1f-7c7f6f76ef2e)

### Available actions

| Action | Description |
|---|---|
| Change Elastic Client | Switch to a different ES connection |
| Filter for empty indexes | Show only indexes with `docs.count = 0` |
| Filter by index pattern | Filter indexes by name pattern |
| Reset filter | Remove active filter |
| Sort | Sort by any column (asc/desc), supports size unit conversion |
| Show Index Mapping | View field mappings and fetch sample data for a selected index |
| Delete an index | Delete a single index (with confirmation) |
| Delete all indexes by filter | Bulk delete all currently filtered indexes (with confirmation) |

## Testing

```bash
vendor/bin/simple-phpunit
```

Unit tests cover:
- `ListSorter` — byte conversion, index sorting by various columns
- `FilterByNameOfIndexes` — pattern matching filter
- `FilterNotEmptyIndexes` — empty index filter

Integration tests require a running Symfony kernel with Elasticsearch connection.

## License

MIT
