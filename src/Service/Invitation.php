<?php 
declare(strict_types=1);

namespace  App\Service;

class Invitation
{
    
    private $config;

    public function __construct(Array $config)
    {
        $this->config = $config;
    }

    public function getConfig() : Array
    {
        return $this->config;
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