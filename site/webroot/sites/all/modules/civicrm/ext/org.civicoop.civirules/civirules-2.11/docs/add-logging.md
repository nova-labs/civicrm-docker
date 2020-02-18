## Introduction

It is possible to add logging to Civirules. You have to develop your own logger for Civirules and this logger is hooked into Civirules and used. This means that you could implement your own specific logger for Civirules.

The logger class should be compatible with PSR3 LoggerInterface. The PSR3 Logger describes a standard behaviour for logging functionality without a concrete implementation. This makes it easier for exchange different implementations in a system. For example CiviCRM 4.5 and 4.6 uses a PSR3 logger which logs to the database and CiviCRM 4.7 is going to use a PSR3 logger to log to a log file.

With this in mind we could easily develop an extension which hooks the default CiviCRM logger into Civirules. But it is also possible to develop an extension with an complete different implementation of the logger.

!!! Note
    Why Not the Standard CiviCRM Logger?
    The reason we are not using the default CiviCRM logger is that in CiviCRM 4.4 there is no PSR3 logger implementation.

By default Civirules will only send erros to the logger. Those errors are exceptions which are caught during processing of a CiviRule or during processing of a delayed CiviRule action.

### Tutorial log messages to screen

In the tutorial below we will implement a logger which logs the messages to the screen. The code for this tutorial can be found at [https://github.com/civicoop/org.civicoop.examplecivirulelogger](https://github.com/civicoop/org.civicoop.examplecivirulelogger).

The first step is achieved by developing an logger which implement the [PSR3 LoggerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#3-psrlogloggerinterface).  

```php
//file CRM/Examplecivirulelogger/PopupLogger
class CRM_Examplecivirulelogger_PopupLogger implements \Psr\Log\LoggerInterface {
}
``` 

### Implement the methods 

The LoggerInterface consist of the following methods which should be implemented:

- emergency
- alert
- critical
- error
- warning
- notice
- info
- debug
- log

All methods have the same kind of structure which takes a message as a parameter and an array with context information. The log method has one extra parameter and that is level. 
We are going to implement the methods in such a way that they are calling the log method with the appropriate level.

```php
/**
  * System is unusable.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function emergency($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::EMERGENCY, $message, $context);
 }
 /**
  * Action must be taken immediately.
  *
  * Example: Entire website down, database unavailable, etc. This should
  * trigger the SMS alerts and wake you up.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function alert($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::ALERT, $message, $context);
 }
 /**
  * Critical conditions.
  *
  * Example: Application component unavailable, unexpected exception.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function critical($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::CRITICAL, $message, $context);
 }
 /**
  * Runtime errors that do not require immediate action but should typically
  * be logged and monitored.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function error($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::ERROR, $message, $context);
 }
 /**
  * Exceptional occurrences that are not errors.
  *
  * Example: Use of deprecated APIs, poor use of an API, undesirable things
  * that are not necessarily wrong.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function warning($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::WARNING, $message, $context);
 }
 /**
  * Normal but significant events.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function notice($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::NOTICE, $message, $context);
 }
 /**
  * Interesting events.
  *
  * Example: User logs in, SQL logs.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function info($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::INFO, $message, $context);
 }
 /**
  * Detailed debug information.
  *
  * @param string $message
  * @param array  $context
  *
  * @return null
  */
 public function debug($message, array $context = array())
 {
   $this->log(\Psr\Log\LogLevel::DEBUG, $message, $context);
 }
```
### Implement the actual log method

The remaining bit of the class is to use [hook_civirules_logger](/hooks/hook_civirules_logger) implement the `log` method in such a way that the log messages is shown as a popup to the user.

```php
function examplecivirulelogger_civirules_logger(\Psr\Log\LoggerInterface &$logger=null) {
  $logger = new CRM_Examplecivirulelogger_PopupLogger();
}
```
## Context parameters from Civirules

Civirules will set the context array with the following data (if it is available in Civirules)
```
- contact_id
- rule_id
- rule_title
```
In case there is a log message from an individual condition the context contains the following:
```
- message
- rule_condition_id
- condition_label
- condition_parameters
```
In case there is a log message from an individual action the context contains the following:
```
- message
- rule_action_id
- action_label
- action_parameters
```
In case of an error the following extra context parameters are available:
```
- reason
- original_error
- exception_message
- file
- line
```

## Adding a message to the Rule form

If you want to add contents to the rule form. E.g. enable logging for that particular rule you could use the hook_civicrm_buildForm and hook_civicrm_postProcess. We have added two functions to the form to set content in the form in the Rule Details Block.

<a href='../img/screenshot_civirules_civirule_form.png'><img alt='my group setup' src='../img/screenshot_civirules_civirule_form.png'/></a>

```php
function examplecivirulelogger_civicrm_buildForm($formName, &$form) {
  if ($form instanceof CRM_Civirules_Form_Rule) {
    $form->setPostRuleBlock("Logging is enabled");
  }
}
```

## Logging from within an action or condition

If you have developed a CiviRules action or condition you can send messages to the logger:

__Logging from a CiviRules action__

```php
public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData){
  $this->logAction('my log message', $triggerData, \PSR\Log\LogLevel::INFO);
  ...
}
```

__Logging from a CiviRules condition__

```php
public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData){
  $this->logCondition('my log message', $triggerData, \PSR\Log\LogLevel::INFO);
  ...
}
```
