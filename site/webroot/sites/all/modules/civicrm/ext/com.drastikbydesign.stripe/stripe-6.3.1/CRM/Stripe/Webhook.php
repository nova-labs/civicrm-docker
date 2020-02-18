<?php
/**
 * https://civicrm.org/licensing
 */

use CRM_Stripe_ExtensionUtil as E;

/**
 * Class CRM_Stripe_Webhook
 */
class CRM_Stripe_Webhook {

  use CRM_Stripe_WebhookTrait;

  /**
   * Checks whether the payment processors have a correctly configured webhook
   *
   * @see stripe_civicrm_check()
   *
   * @param array $messages
   * @param bool $attemptFix If TRUE, try to fix the webhook.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function check(&$messages, $attemptFix = FALSE) {
    $result = civicrm_api3('PaymentProcessor', 'get', [
      'class_name' => 'Payment_Stripe',
      'is_active' => 1,
      'domain_id' => CRM_Core_Config::domainID(),
    ]);

    foreach ($result['values'] as $paymentProcessor) {
      $messageTexts = [];
      $webhook_path = self::getWebhookPath($paymentProcessor['id']);

      $processor = new CRM_Core_Payment_Stripe('', civicrm_api3('PaymentProcessor', 'getsingle', ['id' => $paymentProcessor['id']]));
      $processor->setAPIParams();

      try {
        $webhooks = \Stripe\WebhookEndpoint::all(["limit" => 100]);
      }
      catch (Exception $e) {
        $error = $e->getMessage();
        $messages[] = new CRM_Utils_Check_Message(
          'stripe_webhook',
          $error,
          self::getTitle($paymentProcessor),
          \Psr\Log\LogLevel::ERROR,
          'fa-money'
        );

        continue;
      }

      $found_wh = FALSE;
      foreach ($webhooks->data as $wh) {
        if ($wh->url == $webhook_path) {
          $found_wh = TRUE;
          // Check and update webhook
          try {
            $updates = self::checkWebhook($wh);
            if ($updates && $wh->status != 'disabled') {
              if ($attemptFix) {
                // We should try to update the webhook.
                $messageTexts[] = E::ts('Unable to update the webhook %1. To correct this please delete the webhook at Stripe and then revisit this page which will recreate it correctly.',
                  [1 => urldecode($webhook_path)]
                );
                \Stripe\WebhookEndpoint::update($wh['id'], $updates);
              }
              else {
                $messageTexts[] = E::ts('Problems detected with Stripe webhook %1. Please visit <a href="%2">Fix Stripe Webhook</a> to fix.', [
                  1 => urldecode($webhook_path),
                  2 => CRM_Utils_System::url('civicrm/stripe/fix-webhook'),
                ]);
              }
            }
          }
          catch (Exception $e) {
            $messageTexts[] = E::ts('Could not check/update existing webhooks, got error from stripe <em>%1</em>', [
                1 => htmlspecialchars($e->getMessage())
              ]
            );
          }
        }
      }

      if (!$found_wh) {
        if ($attemptFix) {
          try {
            // Try to create one.
            self::createWebhook($paymentProcessor['id']);
          }
          catch (Exception $e) {
            $messageTexts[] = E::ts('Could not create webhook, got error from stripe <em>%1</em>', [
              1 => htmlspecialchars($e->getMessage())
            ]);
          }
        }
        else {
          $messageTexts[] = E::ts('Stripe Webhook missing! Please visit <a href="%1">Fix Stripe Webhook</a> to fix.<br />Expected webhook path is: <a href="%2" target="_blank">%2</a>',
            [
              1 => CRM_Utils_System::url('civicrm/stripe/fix-webhook'),
              2 => $webhook_path,
            ]
          );
        }
      }

      foreach ($messageTexts as $messageText) {
        $messages[] = new CRM_Utils_Check_Message(
          'stripe_webhook',
          $messageText,
          self::getTitle($paymentProcessor),
          \Psr\Log\LogLevel::WARNING,
          'fa-money'
        );
      }
    }
  }

  /**
   * Get the error message title for the system check
   * @param array $paymentProcessor
   *
   * @return string
   */
  private static function getTitle($paymentProcessor) {
    if (!empty($paymentProcessor['is_test'])) {
      $paymentProcessor['name'] .= ' (test)';
    }
    return E::ts('Stripe Payment Processor: %1 (%2)', [
      1 => $paymentProcessor['name'],
      2 => $paymentProcessor['id'],
    ]);
  }

  /**
   * Create a new webhook for payment processor
   *
   * @param int $paymentProcessorId
   */
  public static function createWebhook($paymentProcessorId) {
    $processor = new CRM_Core_Payment_Stripe('', civicrm_api3('PaymentProcessor', 'getsingle', ['id' => $paymentProcessorId]));
    $processor->setAPIParams();

    $params = [
      'enabled_events' => self::getDefaultEnabledEvents(),
      'url' => self::getWebhookPath($paymentProcessorId),
      'api_version' => CRM_Core_Payment_Stripe::getApiVersion(),
      'connect' => FALSE,
    ];
    \Stripe\WebhookEndpoint::create($params);
  }


  /**
   * Check and update existing webhook
   *
   * @param array $webhook
   * @return array of correction params. Empty array if it's OK.
   */
  public static function checkWebhook($webhook) {
    $params = [];

    if (empty($webhook['api_version']) || ($webhook['api_version'] !== CRM_Core_Payment_Stripe::API_VERSION)) {
      $params['api_version'] = CRM_Core_Payment_Stripe::API_VERSION;
    }

    if (array_diff(self::getDefaultEnabledEvents(), $webhook['enabled_events'])) {
      $params['enabled_events'] = self::getDefaultEnabledEvents();
    }

    return $params;
  }

  /**
   * List of webhooks we currently handle
   * @return array
   */
  public static function getDefaultEnabledEvents() {
    return [
      'invoice.payment_succeeded',
      'invoice.payment_failed',
      'charge.failed',
      'charge.refunded',
      'charge.succeeded',
      'charge.captured',
      'customer.subscription.updated',
      'customer.subscription.deleted',
    ];
  }

}
