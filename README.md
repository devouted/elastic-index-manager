# Elastic Index Manager

This is a simple elastic indexes manager.

## Installation

Install the package using Composer:

```bash
composer require devouted/elastic-index-manager
```


## Configuration
Add the following configuration to your services.yaml to register the command:

```yaml
Devouted\ElasticIndexManager\Command\ElasticIndexManagerCommand:
    tags: ['console.command']
```
Command will search for any service that returns an **Elastic\Elasticsearch\Client** or **Elastic\Elasticsearch\ClientBuilder** class, like below:
```yaml
    elasticsearch.client.default:
        public: true
        class: Elastic\Elasticsearch\ClientBuilder
        factory: [ 'Elastic\Elasticsearch\ClientBuilder', 'create' ]
        calls:
            - [ setHosts, [ [ '%env(ELASTICSEARCH_TRANSPORT)%://%env(ELASTICSEARCH_HOST)%:%env(ELASTICSEARCH_PORT)%' ] ] ]
```


## Usage
Once installed and configured, you can use the command via the Symfony Console to manage your elastic indexes.
```bash
bin/console elasticsearch:index:manage
```

Select connection

![image](https://github.com/user-attachments/assets/4c6bb436-bd08-4800-9bbc-d752f599d08a)

run action

![image2](https://github.com/user-attachments/assets/de3cdc2a-765d-4d22-9c1f-7c7f6f76ef2e)
