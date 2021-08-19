<?php 
declare(strict_types=1);

namespace  App\Service;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Invitation
{
    
    /** @var  String $session_id */
    private $session_id;


    /**
     * constructor
     * 
     * @param Array $config
     * @param LoggerInterface $logger
     * 
     */
    public function __construct(private Array $config, private $env, private LoggerInterface $logger)
    {
        $this->init();
    }

    public function getConfig() : Array
    {
        return $this->config;
    }

    /**
     * instantiates http client
     */
    private function init() : void
    {
        $this->client = new Client();
    }


    /**
     * Gets NeonCRM session id
     *
     * @return String
     */
    public function getSessionId() : String
    {
        if ($this->session_id) {
            return $this->session_id;
        }
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

        $this->session_id = $result->userSessionId;
        return $this->session_id;
  
    }
    /**
     * queries a NAJIT member record
     * 
     * @param String $email
     * @return \stdClass
     * 
     */
    public function findMember(String $email) :? \stdClass
    {
        $session_id = $this->getSessionId();
        $this->logger->debug('logged in with session id: '.$session_id);        
        $endpoint = $this->config['neoncrm.base_uri'] . '/account/listAccounts';
        /* This ugliness apparently can't be avoided because field names are reused 
        multiple times. If we were to create an array, the keys set later would overwrite 
        the ones set earlier */
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
    public function verifyMembership(String $email) :? Array
    {

        $result = $this->findMember($email);
        if ('SUCCESS' != $result->listAccountsResponse->operationResult) {
            throw new \Exception('member query operation failed: '.json_encode($result,JSON_PRETTY_PRINT));
        }

        // if no member record is found...
        if ($result->listAccountsResponse->page->totalResults === 0) {
            $this->logger->info("member not found",['email' => $email]);
            
            if ($email == 'mintz@vernontbludgeon.com') {
                $this->logger->debug("faking Vernon's membership");
                return [
                'member' => [
                    'email' => 'mintz@vernontbludgeon.com',
                    'last_name' => 'Bludgeon',
                    'first_name' => 'Vernon',
                    'expiration_date' => date('Y-m-d',strtotime('+2 years')),
                    'account_id' => 54321
                ],
                'expired' => false,
                ];
            } else {
                return null;
            }
        }
        // else, check expiration. order of columns is not guaranteed, so...
        $objects = $result->listAccountsResponse->searchResults->nameValuePairs[0]->nameValuePair;
        
        $data = [ 'expired' => true,]; // presume expired
        $member = [];
        $props = [
            'Email 1' => 'email',
            'Last Name' => 'last_name',
            'First Name' => 'first_name',
            'Membership Expiration Date' => 'expiration_date',
            'Account ID' => 'account_id',
        ];
        foreach ($objects as $o) {

            $member[$props[$o->name]] = $o->value ?? null;
            if ($o->name == 'Membership Expiration Date' ) {
                if (! isset($o->value)) { // life membership
                    $data['expired']= false;
                } else {
                    $today = date('Y-m-d');
                    $expiration = $o->value;
                    if ($expiration >= $today) {
                        $data['expired'] = false;
                    }
                }
            }
        }
        $data['member'] = $member;
        $this->logger->info("located member",$data);
        return $data;
    }

    /**
     * Sends invitation to create user account.
     * 
     * Uses the Discourse API to create and email an invitation 
     * to create a user account.
     * 
     * @param string $email
     */
    public function sendInvitation(String $email) : \stdClass
    {
        $this->logger->debug("attempting to send invitation to: $email");
        if ($this->env == "dev" and !in_array($email, ['david@davidmintz.org','mintz@vernontbludgeon.com'])) {
            throw new \Exception("you are in dev environment but using an email other than your own: $email");
        }
        $endpoint = $this->config['discourse.base_uri'] . '/invites';
        $headers = [
            'Api-Key' => $this->config['discourse.api_key'],
            'Api-Username' => $this->config['discourse.api_username'],
            'Accept'     => 'application/json', // maybe not required
        ];
        try {
            // first we have to create the invitation, apparently...
            $res = $this->client->request('POST',$endpoint, ['headers' => $headers ]);
            $data = json_decode((string)$res->getBody());
            $id = $data->id;
            $endpoint .= "/$data->id";
            // ...then update it to make it get emailed to someone specific
            $res = $this->client->request('PUT',$endpoint,[
                'headers' => $headers,
                'form_params' => [
                    'email' => $email,  
                    'custom_message'=>	'We received a request to send you this invitation to register for NAJIT\'s Discourse site. '
                        . "If you did not initiate this yourself or if you're not interested, you can simply ignore this message. ",
                    'send_email'=> true,
                    'expires_at' => date('Y-m-d+H:iP', strtotime("tomorrow 9:00 am")),
                ],
            ]);
            $data = json_decode((string)$res->getBody());
            return $data;

        } catch (\Exception $e) {
            $this->logger->error("Exception while sending PUT to $endpoint: ".get_class($e), 
             ['message' => $e->getMessage(), 'trace'=>$e->getTraceAsString()]
            );
            $response = [
                'body' => $data,
                'error' => $e->getMessage(),
                'status' => $res->getStatusCode(),
            ];
            if (stristr($response['error'],'422 Unprocessable')) {
                $response['message'] = "It appears that $email already has a user account on NAJIT's Discourse site";
            } else {
                $response['message'] = "An unexpected application error happened. Please try again later.";
            }
            return (object)$response;
        }
        return $data; 
        // } catch (\Exception $e) {
        //     if (! empty($res)) {
        //         $status = $res->getStatusCode();
        //         if (empty($data)) { 
        //             $data = new \stdClass;
        //         }
        //         $data->status = $res->getStatusCode();
        //         $data->reason = $res->getReasonPhrase();
        //         $this->logger->error("shit happened",(Array)$data);
        //         print $e->getTraceAsString();
        //         return $data;
                
        //     } else {
        //         throw $e;
        //     }
        //     // log it or what have you...
        //     // throw $e;
        // }   
    }

    /**
     * deletes an invitation
     * 
     * @param int $id
     * @return stdClass object
     */
    public function deleteInvitation(int $id) : \stdClass
    {
        $endpoint = $this->config['discourse.base_uri'] . '/invites';
        $headers = [
            'Api-Key' => $this->config['discourse.api_key'],
            'Api-Username' => $this->config['discourse.api_username'],
            'Accept'     => 'application/json', // maybe not required
        ];
        $response = $this->client->request('DELETE',$endpoint,[
            'headers' => $headers,
            'form_params' => ['id' => $id]
        ]);

        return json_decode((string)$response->getBody());
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
curl "https://api.neoncrm.com/neonws/services/api/account/listAccounts?responseType=json&userSessionId=$KEY

&outputfields.idnamepair.id=&outputfields.idnamepair.name=Account%20ID&outputfields.idnamepair.id=

&outputfields.idnamepair.name=First%20Name&outputfields.idnamepair.id=&outputfields.idnamepair.name=Last%20Name

&outputfields.idnamepair.id=&outputfields.idnamepair.name=Email%201&searches.search.key=Email

&searches.search.searchOperator=EQUAL&searches.search.value=natasha.bonilla@gmail.com

&outputfields.idnamepair.name=Membership%20Expiration%20Date"|



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