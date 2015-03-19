<?php

class OrbitalProcessor {
  const AUTH_APPROVED = 1;
  const AUTH_DECLINED = 2;
  const AUTH_ERROR = 3;

  protected $mode = "test"; //in test mode we can try things out without making any real transactions

  public $industry_type;
  public $message_type;
  public $bin;
  public $merchant_id;
  public $terminal_id;
  public $cc_num;
  public $cc_expiry_mon;
  public $cc_expiry_yr;
  public $currency_code;
  public $curency_exp;
  public $cvv;
  public $postal_code;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $phone;
  public $card_owner;
  public $owner_id;
  public $amount;
  public $comments;
  public $card_type;
  public $order_num;
  public $tx_ref_num;

  //provided for debugging purposes
  public $request_xml;
  public $response_xml;

  public $transactionMsg;
  public $responseArray;


  //Process auth and capture payment
  public function processACPayment() {
    $xml = $this->buildXML();
    $this->SubmitTransaction($xml);
  }


  private function SubmitTransaction($xml) {

    $server_environment = variable_get('paymentech_transaction_mode');
    if($server_environment == 'production') {
      $URL = "https://orbital1.paymentech.net";
    } else {
      $URL = "https://orbitalvar1.paymentech.net";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('POST /AUTHORIZE HTTP/1.0','MIME-Version: 1.0', 'Content-type: application/PTI58', 'Document-type: Request', 'Content-transfer-encoding: text', 'Content-length:' . strlen($xml)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_POST, true);

    $this->responsexml = curl_exec($ch);
    $this->responseArray = $this->ParseXmlResponse($this->responsexml);
    if (curl_errno($ch))
    {
      $this->transactionMsg = "Error in processing xml for curl: " . curl_error($ch)."<br>$xml";
      return curl_errno;
    }
    else
    {
      curl_close($ch);
    }
  }


  private function buildXML() {

  $server_environment = variable_get('paymentech_transaction_mode');

  if($server_environment != 'production') {
    //default testing values from orbital documentation
    $xml = '';
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<Request>';
    $xml .= '<NewOrder>';
    $xml .= '<OrbitalConnectionUsername>'.variable_get('paymentech_orbital_test_username').'</OrbitalConnectionUsername>';
    $xml .= '<OrbitalConnectionPassword>'.variable_get('paymentech_orbital_test_password').'</OrbitalConnectionPassword>';
    $xml .= '<IndustryType>'.$this->industry_type.'</IndustryType>';
    $xml .= '<MessageType>'.$this->message_type.'</MessageType>';
    $xml .= '<BIN>'.$this->bin.'</BIN>';
    $xml .= '<MerchantID>'.variable_get('paymentech_merchant_id').'</MerchantID>';
    $xml .= '<TerminalID>001</TerminalID>';
    $xml .= '<CardBrand>VI</CardBrand>';
    $xml .= '<AccountNum>4788250000028291</AccountNum>';
    $xml .= '<Exp>'.$this->cc_expiry_mon . $this->cc_expiry_yr.'</Exp>';
    $xml .= '<CurrencyCode>840</CurrencyCode>';
    $xml .= '<CurrencyExponent>2</CurrencyExponent>';
    $xml .= '<CardSecVal>'.$this->cvv.'</CardSecVal>';
    $xml .= '<AVSzip>'.$this->postal_code.'</AVSzip>';
    $xml .= '<AVSaddress1>'.$this->address1.'</AVSaddress1>';
    $xml .= '<AVScity>'.$this->city.'</AVScity>';
    $xml .= '<AVSstate>'.$this->state.'</AVSstate>';
    $xml .= '<AVSphoneNum>'.$this->phone.'</AVSphoneNum>';
    $xml .= '<AVSname>'.$this->card_owner.'</AVSname>';
    $xml .= '<OrderID>'.$this->ordernum.'</OrderID>';
    $xml .= '<Amount>'.$this->amount.'</Amount>';
    $xml .= '<CustomerEmail></CustomerEmail>';
    $xml .= '</NewOrder>';
    $xml .= '</Request>';
    $this->request_xml=$xml;
    } else {
    $xml = '';
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<Request>';
    $xml .= '<NewOrder>';
    $xml .= '<OrbitalConnectionUsername>'.variable_get('paymentech_orbital_username').'</OrbitalConnectionUsername>';
    $xml .= '<OrbitalConnectionPassword>'.variable_get('paymentech_orbital_password').'</OrbitalConnectionPassword>';
    $xml .= '<IndustryType>'.$this->industry_type.'</IndustryType>';
    $xml .= '<MessageType>'.$this->message_type.'</MessageType>';
    $xml .= '<BIN>'.$this->bin.'</BIN>';
    $xml .= '<MerchantID>'.variable_get('paymentech_merchant_id').'</MerchantID>';
    $xml .= '<TerminalID>'.variable_get('paymentech_terminal_id').'</TerminalID>';
    $xml .= '<CardBrand>'.$this->card_type.'</CardBrand>';
    $xml .= '<AccountNum>'.$this->cc_num.'</AccountNum>';
    $xml .= '<Exp>'.$this->cc_expiry_mon . $this->cc_expiry_yr.'</Exp>';
    $xml .= '<CurrencyCode>840</CurrencyCode>';
    $xml .= '<CurrencyExponent>2</CurrencyExponent>';
    $xml .= '<CardSecVal>'.$this->cvv.'</CardSecVal>';
    $xml .= '<AVSzip>'.$this->postal_code.'</AVSzip>';
    $xml .= '<AVSaddress1>'.$this->address1.'</AVSaddress1>';
    $xml .= '<AVScity>'.$this->city.'</AVScity>';
    $xml .= '<AVSstate>'.$this->state.'</AVSstate>';
    $xml .= '<AVSphoneNum>'.$this->phone.'</AVSphoneNum>';
    $xml .= '<AVSname>'.$this->card_owner.'</AVSname>';
    $xml .= '<OrderID>'.$this->ordernum.'</OrderID>';
    $xml .= '<Amount>'.$this->amount.'</Amount>';
    $xml .= '<CustomerEmail></CustomerEmail>';
    $xml .= '</NewOrder>';
    $xml .= '</Request>';
    $this->request_xml=$xml;
    }
    return $xml;
  }

  private function ParseXmlResponse($xmlstring) {
    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
    xml_parser_set_option($xml_parser,XML_OPTION_SKIP_WHITE,1);
    xml_parse_into_struct($xml_parser, $xmlstring, $vals, $index);
    xml_parser_free($xml_parser);

    foreach($vals as $val)
    {
      $tagval=$val['tag'];
      if(($val['tag']!='QuickResp') && ($val['tag']!='NewOrderResp')) {
        if(isset($val['value'])){
          $newResArr[$tagval]=$val['value'];
          }
          else{
          $newResArr[$tagval]='';
          }
        }
    }
        return $newResArr;
  }
}

?>
