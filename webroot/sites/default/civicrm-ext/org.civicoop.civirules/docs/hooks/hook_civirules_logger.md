# hook_civirules_logger

## Description

This hook is called for returning an object to do logging in CiviRules.
It is invoked as soon as CiviRules is looking for a logger class.

If you want to return a logger you should replace the `$logger` parameter with an instantiated logger object which should be instance of `\Psr\Log\LoggerInterface`.

Set `$logger` to `null` if you want to disable the logging.

## Definition

```php
hook_civirules_logger(\Psr\Log\LoggerInterface &$logger=null)
```

## Parameters

It has one parameter and that is the current Logger, which probably is `null`.


## Returns

-   `NULL`

## Example

The example below returns a database logger for civirules.

```php
function civiruleslogger_civirules_logger(\Psr\Log\LoggerInterface &$logger=null) {
  if (empty($logger)) {
    $logger = new CRM_Civiruleslogger_DatabaseLogger();
  }
}
```
