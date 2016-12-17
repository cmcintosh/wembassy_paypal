<?php

namespace Drupal\wembassy_paypal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
* Provides a controller routine for Instant Payment Notifications from
* Paypal.
*/
class PaypalIPN extends ControllerBase {

  /**
  * Callback for handling incoming IPN.
  */
  public function ipn_link( Request $request ) {

    // Check to see if we have been sent a debug IPN.
    if ($debugIPN = $request->get('debugIPN')) {
      $ipn = $debugIPN;
    }
    else {
      // Continue handling for non-debugging mode.
      $ipn = $request->all();

      // Start preparing the array to POST back to PayPal to validate the IPN.
      $variables = array('cmd=_notify-validate');
      foreach($ipn as $key => $value) {
        $variables[] = $key .'=' . urlencode($value);
      }

      // Next we need to determine the correct PayPal server to respond to.
      if (!empty($ipn['test_ipn']) && $ipn['test_ipn'] == 1) {
        $host = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
      else {
        $host = 'https://www.paypal.com/cgi-bin/webscr';
      }

      // Now send back the process request to validate the IPN
      $client = \Drupal::httpClient();
      try {
        $response = $client->request($host, [ 'data' => implode('&', $variables) ] );

        // @TODO: Come back to this when we are setup for testing.
        // we need to update from the old Drupal 7 method of using drupal_http_response.
        // I have not found clear documentation for a upgrade path yet but will soon.

      }
      catch ( RequestException $e) {
        watchdog_exception('wembassy_paypal', $e);
      }

    }

    return [
      '#markup' => print "Test results:" . print_r($ipn, true)
    ];
  }

}
