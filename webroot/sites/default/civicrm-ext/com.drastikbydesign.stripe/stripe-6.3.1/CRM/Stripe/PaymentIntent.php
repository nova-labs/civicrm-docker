<?php
/**
 * https://civicrm.org/licensing
 */

/**
 * Manage the civicrm_stripe_paymentintent database table which records all created paymentintents
 * Class CRM_Stripe_PaymentIntent
 */
class CRM_Stripe_PaymentIntent {

  /**
   * Add a paymentIntent to the database
   *
   * @param $params
   *
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function add($params) {
    $requiredParams = ['id', 'payment_processor_id'];
    foreach ($requiredParams as $required) {
      if (empty($params[$required])) {
        throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (add): Missing required parameter: ' . $required);
      }
    }

    $count = 0;
    foreach ($params as $key => $value) {
      switch ($key) {
        case 'id':
          $queryParams[] = [$value, 'String'];
          break;

        case 'payment_processor_id':
          $queryParams[] = [$value, 'Integer'];
          break;

        case 'contribution_id':
          if (empty($value)) {
            continue 2;
          }
          $queryParams[] = [$value, 'Integer'];
          break;

        case 'description':
          $queryParams[] = [$value, 'String'];
          break;

        case 'status':
          $queryParams[] = [$value, 'String'];
          break;

        case 'identifier':
          $queryParams[] = [$value, 'String'];
          break;
      }
      $keys[] = $key;
      $update[] = "{$key} = '{$value}'";
      $values[] = "%{$count}";
      $count++;
    }

    $query = "INSERT INTO civicrm_stripe_paymentintent
          (" . implode(',', $keys) . ") VALUES (" . implode(',', $values) . ")";
    $query .= " ON DUPLICATE KEY UPDATE " . implode(',', $update);
    CRM_Core_DAO::executeQuery($query, $queryParams);
  }

  /**
   * @param array $params
   *
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function create($params) {
    self::add($params);
  }

  /**
   * Delete a Stripe paymentintent from the CiviCRM database
   *
   * @param array $params
   *
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function delete($params) {
    $requiredParams = ['id'];
    foreach ($requiredParams as $required) {
      if (empty($params[$required])) {
        throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (delete): Missing required parameter: ' . $required);
      }
    }

    $queryParams = [
      1 => [$params['id'], 'String'],
    ];
    $sql = "DELETE FROM civicrm_stripe_paymentintent WHERE id = %1";
    CRM_Core_DAO::executeQuery($sql, $queryParams);
  }

  /**
   * @param array $params
   * @param \CRM_Core_Payment_Stripe $stripe
   *
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function stripeCancel($params, $stripe) {
    $requiredParams = ['id'];
    foreach ($requiredParams as $required) {
      if (empty($params[$required])) {
        throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (getFromStripe): Missing required parameter: ' . $required);
      }
    }

    $stripe->setAPIParams();

    $intent = \Stripe\PaymentIntent::retrieve($params['id']);
    $intent->cancel();
  }

  /**
   * @param array $params
   * @param \CRM_Core_Payment_Stripe $stripe
   *
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function stripeGet($params, $stripe) {
    $requiredParams = ['id'];
    foreach ($requiredParams as $required) {
      if (empty($params[$required])) {
        throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (getFromStripe): Missing required parameter: ' . $required);
      }
    }

    $stripe->setAPIParams();

    $intent = \Stripe\PaymentIntent::retrieve($params['id']);
    $paymentIntent = self::get($params);
    $params['status'] = $intent->status;
    self::add($params);
  }

  /**
   * Get an existing Stripe paymentIntent from the CiviCRM database
   *
   * @param $params
   *
   * @return array
   * @throws \Civi\Payment\Exception\PaymentProcessorException
   */
  public static function get($params) {
    $requiredParams = ['id'];
    foreach ($requiredParams as $required) {
      if (empty($params[$required])) {
        throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (get): Missing required parameter: ' . $required);
      }
    }
    if (empty($params['contact_id'])) {
      throw new \Civi\Payment\Exception\PaymentProcessorException('Stripe PaymentIntent (get): contact_id is required');
    }
    $queryParams = [
      1 => [$params['id'], 'String'],
    ];

    $dao = CRM_Core_DAO::executeQuery("SELECT *
      FROM civicrm_stripe_paymentintent
      WHERE id = %1", $queryParams);

    return $dao->toArray();
  }

}
