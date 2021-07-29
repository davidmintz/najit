<?php 
declare(strict_types=1);

namespace  App\Service;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Invitation
{
    
    private $config;

    /** @var GuzzleHttp\Client */
    private $client;

    /** @var  Psr\Log\LoggerInterface */
    private $logger;

    /**
     * constructor
     * 
     * 
     */

    public function __construct(Array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger; 
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
            throw new \Exception('login operation failed: '.json_encode($result,JSON_PRETTY_PRINT));
        }

       
        return $result->userSessionId;
  
    }
    /**
     * queries a NAJIT member record
     * 
     * 
     */
    public function findMember(String $email) :? \stdClass
    {
        $session_id = $this->login();
        $this->logger->debug('logged in with session id: '.$session_id);
        dump('logged in with session id: '.$session_id);
        // if (! $email ) { $email = 'yarmila13@comcast.net'; }
        dump ('using email: '.$email);
        $endpoint = $this->config['neoncrm.base_uri'] . '/account/listAccounts';
        $query_parts = [
            "userSessionId=$session_id",
            'responseType=json',         
            'searches.search.key=Email',
            'searches.search.searchOperator=EQUAL', //CONTAIN
            // 'searches.search.value=natasha.bonilla@gmail.com',
            // yarmila13@comcast.net
            // info@amirshahilaw.com
            // amirshahi@y7mail.com
            'searches.search.value='.$email,
            'outputfields.idnamepair.name=Account%20ID',
            'outputfields.idnamepair.id=',
            'outputfields.idnamepair.name=Membership%20Expiration%20Date',
            'outputfields.idnamepair.name=First%20Name',
            'outputfields.idnamepair.name=Last%20Name',
            'outputfields.idnamepair.name=Email%201',
        ];
        $string = implode('&',$query_parts);
        $endpoint .= '?' . $string;
        $res = $this->client->request('GET',$endpoint);
        $response = json_decode((string)$res->getBody());

        return $response;
    }

    /**
     * checks that $email belongs to an active member
     * 
     * @param String $email
     * @return Array
     */
    public function verifyMembership(String $email = 'info@amirshahilaw.com') :? Array
    {
        $session_id = $this->login();
        $result = $this->findMember($email);
        //  dump(get_object_vars($result)); return $result;
        if ('SUCCESS' != $result->listAccountsResponse->operationResult) {
            throw new \Exception('member query operation failed: '.json_encode($result,JSON_PRETTY_PRINT));
        }
        // if no member record is found...
        if ($result->listAccountsResponse->page->totalResults === 0) {
            dump("not found");
            return null;
        }
        // else, check expiration. order of columns is not guaranteed, so...
        $objects = $result->listAccountsResponse->searchResults->nameValuePairs[0]->nameValuePair;
        
        $return = [];
        foreach ($objects as $o) {
            $return[$o->name] = $o->value ?? null;

            if ($o->name == 'Membership Expiration Date' ) {
                if (! $o->value) {
                    //dump("Life member?");
                    $return['valid']= true;
                } else {
                    $today = date('Y-m-d');
                    $expiration = $o->value;
                    if ($expiration > $today) {
                        $return['valid'] = true;
                    }
                }
            }
        }

        return $return;
    }

    public function sendInvitation(String $email) : \stdClass
    {
        $endpoint = $this->config['discourse.base_uri'] . '/invites';
        $headers = [
            'Api-Key' => $this->config['discourse.api_key'],
            'Api-Username' => $this->config['discourse.api_username'],
            'Accept'     => 'application/json', // maybe

        ];
        try {
            // first we have to create the invitation, apparently...
            $res = $this->client->request('POST',$endpoint, [
                'headers' => $headers
            ]);
            $data = json_decode((string)$res->getBody());
    
            $id = $data->id;
            $endpoint .= "/$data->id";
            dump("gonna PUT $endpoint");
            // ...then update it to make it get emailed to someone specific
            $res = $this->client->request('PUT',$endpoint,[
                'headers' => $headers,
                'form_params' => [
                    'email' => $email,  
                    'custom_message'=>	"This would be your personal message.",
                    'send_email'=> true,
                ],
            ]);
            $data = json_decode((string)$res->getBody());

            return $data;

        } catch (\Exception $e) {
            // log it or what have you...
            throw $e;
        }   
    }   

}
/* 



https://api.neoncrm.com/neonws/services/api/account/listAccounts?
responseType=json
userSessionId=$KEY
outputfields.idnamepair.id=
outputfields.idnamepair.name=Account%20ID
outputfields.idnamepair.id=
outputfields.idnamepair.name=First20Name
utputfields.idnamepair.id=
outputfields.idnamepair.name=Last%20Name
outputfields.idnamepair.id=
outputfields.idnamepair.name=Email%201
searches.search.key=Email
searches.search.searchOperator=CONTAIN
searches.search.value=bo
outputfields.idnamepair.name=Membership%20Expiration%20Date"|

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


{
  "id": 30,
  "invite_key": "58JURk7cki",
  "link": "https://najit.courtinterpreter.net/invites/58JURk7cki",
  "max_redemptions_allowed": 1,
  "redemption_count": 0,
  "created_at": "2021-07-29T01:42:55.826Z",
  "updated_at": "2021-07-29T01:42:55.826Z",
  "expires_at": "2021-08-28T01:42:55.826Z",
  "expired": false,
  "topics": [],
  "groups": []
}



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