<?php

/**
 * @file
 * @class UcOrbitalGateway
 */

/**
 * Chase Paymentech Orbital Gateway Class.
 */
class UcOrbitalGateway {

  public $industryType;
  public $messageType;
  public $bin;
  public $ccNumber;
  public $ccExpireMonth;
  public $ccExpireYear;
  public $currencyCode;
  public $cvv;
  public $postalCode;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $phone;
  public $cardOwner;
  public $amount;
  public $comments;
  public $cardType;
  public $txReferenceNumber;

  public $requestXml;

  public $transactionMsg;
  public $responseArray;

  /**
   * Build XML and POST to the Chase Paymentech Orbital Servers.
   */
  public function processPayment() {
    $xml = $this->buildXML();
    $this->submitTransaction($xml);
  }

  /**
   * Callback to POST to the XML to the Chase Paymentech Orbital servers.
   */
  private function submitTransaction($xml) {

    $server_environment = variable_get('uc_orbital_gateway_transaction_mode');
    if ($server_environment == 'production') {
      $url = "https://orbital1.paymentech.net";
    }
    else {
      $url = "https://orbitalvar1.paymentech.net";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'POST /AUTHORIZE HTTP/1.0', 'MIME-Version: 1.0',
      'Content-type: application/PTI58',
      'Document-type: Request',
      'Content-transfer-encoding: text',
      'Content-length:' . strlen($xml),
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    $this->responsexml = curl_exec($ch);
    $this->responseArray = $this->parseXmlResponse($this->responsexml);
    if (curl_errno($ch)) {
      $this->transactionMsg = "Error in processing xml for curl: " . curl_error($ch) . "<br>$xml";
      return curl_errno;
    }
    else {
      curl_close($ch);
    }
  }

  /**
   * Generates the XML formatted to the Orbital Certification documentation.
   */
  private function buildXML() {

    $server_environment = variable_get('uc_orbital_gateway_transaction_mode');

    if ($server_environment != 'production') {
      // Default testing values from Chase orbital certification documentation.
      $xml = '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<Request>';
      $xml .= '<NewOrder>';
      $xml .= '<OrbitalConnectionUsername>' . decrypt(variable_get('uc_orbital_gateway_test_username')) . '</OrbitalConnectionUsername>';
      $xml .= '<OrbitalConnectionPassword>' . decrypt(variable_get('uc_orbital_gateway_test_password')) . '</OrbitalConnectionPassword>';
      $xml .= '<IndustryType>' . $this->industryType . '</IndustryType>';
      $xml .= '<MessageType>' . $this->messageType . '</MessageType>';
      $xml .= '<BIN>' . $this->bin . '</BIN>';
      $xml .= '<MerchantID>' . decrypt(variable_get('uc_orbital_gateway_merchant_id')) . '</MerchantID>';
      $xml .= '<TerminalID>001</TerminalID>';
      $xml .= '<CardBrand>VI</CardBrand>';
      $xml .= '<AccountNum>4788250000028291</AccountNum>';
      $xml .= '<Exp>' . $this->ccExpireMonth . $this->ccExpireYear . '</Exp>';
      $xml .= '<CurrencyCode>840</CurrencyCode>';
      $xml .= '<CurrencyExponent>2</CurrencyExponent>';
      $xml .= '<CardSecVal>' . $this->cvv . '</CardSecVal>';
      $xml .= '<AVSzip>' . $this->postalCode . '</AVSzip>';
      $xml .= '<AVSaddress1>' . $this->address1 . '</AVSaddress1>';
      $xml .= '<AVScity>' . $this->city . '</AVScity>';
      $xml .= '<AVSstate>' . $this->state . '</AVSstate>';
      $xml .= '<AVSphoneNum>' . $this->phone . '</AVSphoneNum>';
      $xml .= '<AVSname>' . $this->cardOwner . '</AVSname>';
      $xml .= '<OrderID>' . $this->ordernum . '</OrderID>';
      $xml .= '<Amount>' . $this->amount . '</Amount>';
      $xml .= '<CustomerEmail></CustomerEmail>';
      $xml .= '</NewOrder>';
      $xml .= '</Request>';
      $this->requestXml = $xml;
    }
    else {
      $xml = '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<Request>';
      $xml .= '<NewOrder>';
      $xml .= '<OrbitalConnectionUsername>' . decrypt(variable_get('uc_orbital_gateway_username')) . '</OrbitalConnectionUsername>';
      $xml .= '<OrbitalConnectionPassword>' . decrypt(variable_get('uc_orbital_gateway_password')) . '</OrbitalConnectionPassword>';
      $xml .= '<IndustryType>' . $this->industryType . '</IndustryType>';
      $xml .= '<MessageType>' . $this->messageType . '</MessageType>';
      $xml .= '<BIN>' . $this->bin . '</BIN>';
      $xml .= '<MerchantID>' . decrypt(variable_get('uc_orbital_gateway_merchant_id')) . '</MerchantID>';
      $xml .= '<TerminalID>' . decrypt(variable_get('uc_orbital_gateway_terminal_id')) . '</TerminalID>';
      $xml .= '<CardBrand>' . $this->cardType . '</CardBrand>';
      $xml .= '<AccountNum>' . $this->ccNumber . '</AccountNum>';
      $xml .= '<Exp>' . $this->ccExpireMonth . $this->ccExpireYear . '</Exp>';
      $xml .= '<CurrencyCode>840</CurrencyCode>';
      $xml .= '<CurrencyExponent>2</CurrencyExponent>';
      $xml .= '<CardSecVal>' . $this->cvv . '</CardSecVal>';
      $xml .= '<AVSzip>' . $this->postalCode . '</AVSzip>';
      $xml .= '<AVSaddress1>' . $this->address1 . '</AVSaddress1>';
      $xml .= '<AVScity>' . $this->city . '</AVScity>';
      $xml .= '<AVSstate>' . $this->state . '</AVSstate>';
      $xml .= '<AVSphoneNum>' . $this->phone . '</AVSphoneNum>';
      $xml .= '<AVSname>' . $this->cardOwner . '</AVSname>';
      $xml .= '<OrderID>' . $this->ordernum . '</OrderID>';
      $xml .= '<Amount>' . $this->amount . '</Amount>';
      $xml .= '<CustomerEmail></CustomerEmail>';
      $xml .= '</NewOrder>';
      $xml .= '</Request>';
      $this->requestXml = $xml;
    }
    return $xml;
  }

  /**
   * Parses out the XML response and returns an array.
   */
  private function parseXmlResponse($xmlstring) {
    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($xml_parser, $xmlstring, $vals, $index);
    xml_parser_free($xml_parser);

    foreach ($vals as $val) {
      $tag_val = $val['tag'];
      if (($val['tag'] != 'QuickResp') && ($val['tag'] != 'NewOrderResp')) {
        if (isset($val['value'])) {
          $new_response_array[$tag_val] = $val['value'];
        }
        else {
          $new_response_array[$tag_val] = '';
        }
      }
    }
    return $new_response_array;
  }

}
