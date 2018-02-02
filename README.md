# Class LoadDatabase - Documentation

## Table of Contents

* [LoadDatabase](#loaddatabase)
    * [__construct](#__construct)
    * [requireTables](#requiretables)
    * [requireRowsByID](#requirerowsbyid)
    * [requireRowsByCondition](#requirerowsbycondition)

## LoadDatabase

Database loader class for Ticketsystem



* Full name: \ramon1611\Libs\Ticket\LoadDatabase


### __construct

Constructor

```php
LoadDatabase::__construct( mixed $dbObject = NULL, mixed $queryGenerator = NULL, string $defaultOperator = &#039;AND&#039; ): void
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dbObject` | **mixed** | Instance of a Yadal class. If NULL: Instance in $GLOBALS['db'] is used if present. Default is NULL |
| `$queryGenerator` | **mixed** | Instance of an SQLQueryBuilder class. If NULL: Instance in $GLOBALS['query'] is used if present. Default is NULL |
| `$defaultOperator` | **string** | Sets the default operator for conditions. Default is 'AND' |




---

### requireTables

Loads the given tables from database and returns them as associative array like [ 'tableName' => _TABLE_DATA_ ].

```php
LoadDatabase::requireTables( array $dbTables = self::ALL_TABLES ): array
```

If only one table is given then it returns only the _TABLE_DATA_


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dbTables` | **array** | Array of table names to load. Default is LoadDatabase::ALL_TABLES |




---

### requireRowsByID

Loads rows from the given tables where the associated ID is set and returns them

```php
LoadDatabase::requireRowsByID( array $dbTablesIDs, string $idColumnsName = &#039;id&#039; ): array
```

<pre>pattern of $dbTablesIDs:
[
     'exampleTbl1' => 1,
     'exampleTbl2' => 9
]</pre>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dbTablesIDs` | **array** | Array of tables with the associated ID |
| `$idColumnsName` | **string** | The name of the ID-columns. Default is 'id' |



**See Also:**

* \ramon1611\Libs\Ticket\LoadDatabase::requireRowsByCondition() - If the ID-columns does not have the same name

---

### requireRowsByCondition

Loads rows from the given tables where the associated condition is true and returns them

```php
LoadDatabase::requireRowsByCondition( array $dbTablesConditions ): array
```

<pre>pattern of $dbTablesConditions:
[
     'tbl1' => [ 'exampleID' => 1 ],                        # Single Condition
                                                            -> SELECT * FROM tbl1 WHERE exampleID=1;
     'tbl2' => [                                            # Multiple Conditions (default operator)
                 'ID' => 9,                                 -> SELECT * FROM tbl2 WHERE
                 'col2' => 'example'                           ID=9 AND col2='example';
     ],
     'tbl3' => [                                            # Multiple Conditions with custom operator
                 '::ConditionOperator::' => 'OR',           -> SELECT * FROM tbl3 WHERE
                 't3ID'    => 9,                               t3ID=9 OR
                 'default' => 1                                default=1;
     ],
     'tbl4' => [ '::CustomCondition::' => 'name=\'test\'' ] # Custom Condition string
]                                                           -> SELECT * FROM tbl4 WHERE name='test';</pre>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dbTablesConditions` | **array** | Array of tables with the associated condition(s) |




---



--------
> This document was automatically generated from source code comments on 2018-02-02 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
