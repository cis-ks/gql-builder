# PHP GraphQL Query-Builder

Inspired by the Builder-Part of the [PHP GraphQL Client by mghonemiy](https://github.com/mghoneimy/php-graphql-client) this builder provides a simple way to generate Graph-QL-Queries and provide the string.

This library don't include any Client or Response-Decoding functionality and requires the handling of Client-Request and -Responses separately.

## Installation

```shell
composer require cis-bv/gql-builder
```

## Usage

The main entry point for usage is the Query-class. You can create a Query with either a static or typical instantiate call.

### Create a Query 

To create a simple query (here querying a ``device_list`` and retrieve ``id`` and ``name`` of each device) you have three ways:

```php
use Cis\GqlBuilder\Query;

$query = new Query('device_list');
$query->setSelectionSet(['id', 'name']);

// or in one line:
$query = (new Query('device_list'))->setSelectionSet(['id', 'name']);

// or as a static Call:
$query = Query::create(name: 'device_list', selectionSet: ['id', 'name']);

// This is not part of the library
$graphQlClient->sendRequest(['query' => (string)$query]);
```

The example above will create the following query:
```
query{device_list{id name}}
```

If you set the Pretty-Flag:

```php
use Cis\GqlBuilder\Query;
$query = Query::create('device_list', selectionSet: ['id', 'name'])->setOutputFlags(Query::QUERY_PRETTY_PRINT);
```

The query will have a pretty output:

```
query {  
    device_list {  
        id  
        name  
    }  
}  
```

Please keep in mind that these examples are the easiest implementation that it is possible and the "global" query will only be set if scalar fields are within the Selection-Set.

The clean and correct way (and also a more complex example) will be the generation of an "empty" root-query and define all Queries within the selection set:

```php
use Cis\GqlBuilder\Query;
use Cis\GqlBuilder\Parts\Argument;
use Cis\GqlBuilder\Parts\Variable;
use Cis\GqlBuilder\Enums\VariableTypes;

$query = (new Query())->setSelectionSet([
    (new Query('device_list'))
        ->setSelectionSet([
            'id',
            'name',
            (new Query('interfaces'))->setSelectionSet(['id', 'name']),    
        ])
        ->setArguments([
            new Argument('filters', [
                'name' => ['i_starts_with' => '$name'],
                'has_primary_ip' => true
            ], isQueryType: true),
        ]),
])->setVariables([
    new Variable('name', VariableTypes::String, true),
]);
```

The example above also includes Arguments for Filtering (here a more complex FilterQueryType) and using a Variable.

The following query (when set to Pretty Print) will be generated and is a real life example for a Strawberry GraphQL-Endpoint:

```
query ($name: String!) {
    device_list (filters: {name: {i_starts_with: $name},has_primary_ip: true}) {
        id
        name
        interfaces {
            id
            name
        }
    }
}
```

### Static Query-Builder

Within a selection Set you generally can create a new Query with the following code:

```php
use Cis\GqlBuilder\Query;

...
    (new Query('interfaces'))->setSelectionSet(['id', 'name'])
...
```

But you can also use a static build:

```php
use Cis\GqlBuilder\Query;

...
    Query::query('interfaces', ['id', 'name'])
...
```

This is a short version and will return a new Query Instance.

Earlier we also have seen the `` Query::create(...$parameters)`` statement. Another Advantage of this call is that Variables can be easily created as Arrays without the `` new Variable(...$parameters) `` statement.

For easier understanding the named parameter style of PHP8 is used:
```php
use Cis\GqlBuilder\Query;
use Cis\GqlBuilder\Enums\VariableTypes;

$query = Query::create(
    name: 'device_list',
    selectionSet: [
        'id',
        'name',
        'status',
        Query::query(
            name: 'interfaces',
            selectionSet: ['id', 'name'],
            arguments: [
                ['filters', ['name' => ['exact' => '$name']], true]
            ]
        )
    ],
    variables: [
        ['name', VariableTypes::String, true]
    ]
);
```




## 