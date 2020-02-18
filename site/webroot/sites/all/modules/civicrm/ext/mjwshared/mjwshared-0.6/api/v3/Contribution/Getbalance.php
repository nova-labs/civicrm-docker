<?php
/**
 * Action payment.
 *
 * @param array $params
 *
 * @return array
 *   API result array.
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_contribution_getbalance($params) {
  $result['id'] = $params['id'];
  $result['total'] = (float) CRM_Price_BAO_LineItem::getLineTotal($params['id']);
  $result['paid'] = (float) CRM_Core_BAO_FinancialTrxn::getTotalPayments($params['id'], TRUE) ?: 0;
  $result['balance'] = $result['total'] - $result['paid'];
  $result['currency'] = civicrm_api3('Contribution', 'getvalue', [
    'return' => 'currency',
    'id' => $params['id'],
  ]);
  $result['currencySymbol'] = CRM_Core_BAO_Country::defaultCurrencySymbol($result['currency']);

  return civicrm_api3_create_success($result, $params);
}

/**
 * Action payment.
 *
 * @param array $params
 *
 * @return array
 */
function _civicrm_api3_contribution_getbalance_spec(&$params) {
  $idField = civicrm_api3('Contribution', 'getfield', [
    'name' => "id",
    'action' => "get",
  ]);
  $params['id'] = $idField['values'];
  $params['id']['api.required'] = 1;
}
