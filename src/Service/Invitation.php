<?php 
declare(strict_types=1);

namespace  App\Service;
use GuzzleHttp\Client;

class Invitation
{
    
    private $config;

    /** @var GuzzleHttp\Client */
    private $client;

    public function __construct(Array $config)
    {
        $this->config = $config;
        $this->init();
    }

    public function getConfig() : Array
    {
        return $this->config;
    }

    private function init()
    {
        $this->client = new Client();

    }

    /**
     * Gets NeonCRM session id
     *
     * @return String
     */
    public function login() : String
    {
        $endpoint = $this->config['neoncrm.base_uri'] . '/common/login';
        $res = $this->client->request('GET',$endpoint,[
           'query'=>[
            'login.apiKey' => $this->config['neoncrm.api_key'],
            'login.orgid' => $this->config['neoncrm.api_id'],
           ] 
        ]);

        $response = json_decode((string)$res->getBody());
        $result = $response->loginResponse;
        if ('SUCCESS' != $result->operationResult) {
            throw new \EXception('login operation failed: '.json_encode($result,JSON_PRETTY_PRINT));
        }

        return $result->userSessionId;
    }
    /** for initial testing/debugging */
    public function doShit()
    {
        $session_id = $this->login();
        $endpoint = $this->config['neoncrm.base_uri'] . '/account/listAccounts';
        $query_parts = [
            "userSessionId=$session_id",
            "userSessionId=5d7acebbd84f2963b52e4f4f34931e44",
            'responseType=json',
            'responseType=json',
            'userSessionId=$KEY',            
            'searches.search.key=Email',
            'searches.search.searchOperator=EQUAL',
            'searches.search.value=natasha.bonilla@gmail.com',
            // 'searches.search.value=david@davidmintz.org',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=Membership%20Expiration%20Date',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=Account%20ID',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=First%20Name',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=Last%20Name',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=Email%201',
        ];
        $string = implode('&',$query_parts);
        $endpoint .= '?' . $string;
        $res = $this->client->request('GET',$endpoint);
        $response = json_decode((string)$res->getBody());

        return $response;



    }

    public function verifyMembership(String $email)
    {


    }


}
/* 

# notes to self

[ NeonCRM login ]
curl "https://api.neoncrm.com/neonws/services/api/common/login?login.apiKey=3a620bda26b9a82d491228570ab4d9ac&login.orgid=najit"
[response]
{
    "loginResponse": {
      "operationResult": "SUCCESS",
      "responseMessage": "User logged in.",
      "responseDateTime": "2021-07-25T19:15:06.307-05:00",
      "userSessionId": "ae01088882c312947fae45c5b6d37256"
    }
  }
  
[ query member record using email ]
curl "https://api.neoncrm.com/neonws/services/api/account/listAccounts?responseType=json&userSessionId=$KEY&outputfields.idnamepair.id=&outputfields.idnamepair.name=Account%20ID&outputfields.idnamepair.id=&outputfields.idnamepair.name=First%20Name&outputfields.idnamepair.id=&outputfields.idnamepair.name=Last%20Name&outputfields.idnamepair.id=&outputfields.idnamepair.name=Email%201&searches.search.key=Email&searches.search.searchOperator=EQUAL&searches.search.value=natasha.bonilla@gmail.com&outputfields.idnamepair.name=Membership%20Expiration%20Date"|



POST https://najit.courtinterpreter.net/invites
[no parameters]
[response]
	
id	24
invite_key	"F6UHepS2af"
link	"https://najit.courtinterpreter.net/invites/F6UHepS2af"
max_redemptions_allowed	1
redemption_count	0
created_at	"2021-07-25T23:42:39.863Z"
updated_at	"2021-07-25T23:42:39.863Z"
expires_at	"2021-08-24T23:42:39.862Z"
expired	false
topics	[]
groups	[]

[then...]
PUT https://najit.courtinterpreter.net/invites/24
[request parameters]	
email	"mintz@vernontbludgeon.com"
expires_at	"2021-07-26+08:00-04:00"
custom_message	"here+is+your+personal+message"
send_email	"true"

[response]

id	24
invite_key	"F6UHepS2af"
link	"https://najit.courtinterpreter.net/invites/F6UHepS2af"
email	"mintz@vernontbludgeon.com"
emailed	true
custom_message	"here is your personal message"
created_at	"2021-07-25T23:42:39.863Z"
updated_at	"2021-07-25T23:46:44.257Z"
expires_at	"2021-07-26T12:00:00.000Z"
expired	false
topics	[]
groups	[]



*/