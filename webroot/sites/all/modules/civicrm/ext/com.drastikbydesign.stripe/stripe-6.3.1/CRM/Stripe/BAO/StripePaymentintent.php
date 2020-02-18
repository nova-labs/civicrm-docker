<?php
use CRM_Stripe_ExtensionUtil as E;

class CRM_Stripe_BAO_StripePaymentintent extends CRM_Stripe_DAO_StripePaymentintent {

  public static function getEntityName() {
    return 'StripePaymentintent';
  }
  /**
   * Create a new StripePaymentintent based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Stripe_DAO_StripePaymentintent|NULL
   *
  public static function create($params) {
    $className = 'CRM_Stripe_DAO_StripePaymentintent';
    $entityName = 'StripePaymentintent';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  public static function test() {
  }

  /**
   * Create a new StripePaymentintent based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return \CRM_Stripe_BAO_StripePaymentintent
   */
  public static function create($params) {
    $instance = new self;
    try {
      if (!empty($params['id'])) {
        $instance->id = $params['id'];
      }
      elseif ($params['paymentintent_id']) {
        $instance->id = civicrm_api3('StripePaymentintent', 'getvalue', [
          'return' => "id",
          'paymentintent_id' => $params['paymentintent_id'],
        ]);
      }
      if ($instance->id) {
        if ($instance->find()) {
          $instance->fetch();
        }
      }
    }
    catch (Exception $e) {
      // do nothing, we're creating a new one
    }

    $flags = empty($instance->flags) ? [] : unserialize($instance->flags);
    if (!empty($params['flags']) && is_array($params['flags'])) {
      foreach ($params['flags'] as $flag) {
        if (!in_array($flag, $flags)) {
          $flags[] = 'NC';
        }
      }
      unset($params['flags']);
    }
    $instance->flags = serialize($flags);

    $hook = empty($instance->id) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, self::getEntityName(), CRM_Utils_Array::value('id', $params), $params);
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, self::getEntityName(), $instance->id, $instance);

    return $instance;
  }
}
