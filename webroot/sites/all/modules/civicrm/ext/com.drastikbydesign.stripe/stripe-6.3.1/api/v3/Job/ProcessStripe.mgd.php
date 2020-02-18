<?php

return [
  0 =>
  [
    'name' => 'ProcessStripe',
    'entity' => 'Job',
    'params' =>
    [
      'version' => 3,
      'name' => 'ProcessStripe',
      'description' => 'Process Stripe functions',
      'run_frequency' => 'Hourly',
      'api_entity' => 'Job',
      'api_action' => 'process_stripe',
      'parameters' => 'delete_old=-3 month
cancel_incomplete=-1 hour',
    ],
  ],
];
