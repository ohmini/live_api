<?php
date_default_timezone_set("Asia/Bangkok");

define('CONFIG_CURL_CONNECT_TIMEOUT', 3);
define('CONFIG_CURL_TIMEOUT', 10);

define('INIT_VECTOR','EF0354RAG5AES29J');
define('API_KEY','NX32556234ESZPW9');

define('TOKEN_KEY','EAX@1345t2gAZf');
define('TOKEN_KEY_EXPIRE','+5 minutes');

$date = new DateTime();
$token_appid = "true4u";
$token_uid = "000000001";  # User ID
$token_sessionid = "A00000001";  # Session ID
$token_channelid = "207";
$token_datetime = $date->getTimestamp();
$token_string = $token_appid."-".$token_uid."-".$token_sessionid."-".$token_channelid."-".$token_datetime;
$token_encode = token_encode($token_string,TOKEN_KEY);

$input_data = array(	'uid' => '000000001',    // random str+digit -- legnth 10 
				'sessionid' => 'A00000001',           // same as uid
				'appid' => 'true4u',
				'channelid' => '207',
				'langid' => 'th',
				'streamlvl' => 'auto',
				'type' => 'live',
				'stime' => 'null',
				'duration' => 'null',
				'csip' => '203.144.185.201',          // real Client IP address
				'geoblock' => 'false',
				'gps' => 'null',
				'agent' => 'null',
				'visitor' => 'web',
				'charge' => 'true',
				'sec_token' => $token_encode,
				'sec_time' => $token_datetime
			);

$input_json_data = json_encode($input_data);

$MCrypt = new MCrypt(INIT_VECTOR,API_KEY);
$input_data = $MCrypt->encrypt($input_json_data);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://hproxy.stm.trueid.net/proxy/v2/secs_streamingprovider.php",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n$input_data\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
  CURLOPT_HTTPHEADER => array(
    "Postman-Token: 86facf1a-cbe3-4d90-b601-98a051f50b4b",
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
  ),
));

$tv_response = curl_exec($curl);
$tv_response = json_decode($tv_response);
$tv_response = $tv_response->result;
$err = curl_error($curl);

curl_close($curl);

http_response_code(200);

echo($tv_response);

class MCrypt {
    private $iv = 'EF0354RAG5AES29J';	# Initialization Vector
    private $key = 'EAGXX';	# Key

    function __construct($iv='',$key='') {
      if(!empty($iv))
      {
        $this->iv = $iv;
      }
      if(!empty($key))
      {
        $this->key = $key;
      }
    }

    public function encrypt($str) {
        $str = $this->padString($str);
        $encrypted = openssl_encrypt($str, 'aes-128-cbc', $this->key, OPENSSL_ZERO_PADDING, $this->iv);
        return $encrypted;
    }

    public function decrypt($code) {
        $decrypted = openssl_decrypt($code, 'aes-128-cbc', $this->key, OPENSSL_ZERO_PADDING, $this->iv);
        return utf8_encode(rtrim(($decrypted),"\x00..\x1F "));
    }

    protected function hex2bin($hexdata) {
        $bindata = '';
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    private function padString($source) {
        $paddingChar = ' ';
        $size = 16;
        $x = strlen($source) % $size;
        $padLength = $size - $x;
        for ($i = 0; $i < $padLength; $i++) {
            $source .= $paddingChar;
        }
        return $source;
    }

  	private function removepad($source)
  	{
  		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $source);
  	}

}

function token_encode($data,$key)
{
  $result=null;
  $result = md5(sha1(base64_encode($data."|".$key)));
  return $result;
}
?>