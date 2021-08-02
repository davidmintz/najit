<?php declare(strict_types=1);

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Service\Invitation;
use PhpParser\Node\Expr\Cast\Array_;

final class ClientTest extends KernelTestCase
{
    
    private static $client;

    /**
     * @var string a life member's email address
     */
    private String $life_member_email = 'david@davidmintz.org';

    /** 
     * @var string a random member's email address
     * 
     * this will break if this guy's membership should expire
     */ 
    private String  $normal_member_email = 'info@amirshahilaw.com';

    /**
     * 
     */
    private static int $invitation_id;


    public static function setUpBeforeClass() : void
    {
        $container = static::getContainer();
        self::$client = $container->get(Invitation::class);
    }

    public function setUp() : void
    {
        self::bootKernel();
    }

    public function testInvitationClientObjectCanBeInstantiated() : void
    {
        $this->assertTrue(is_object(self::$client));
        
    }

    public function testNeonCrmLogin() : String
    {
        $session_id = self::$client->getSessionId();
        $this->assertIsString($session_id);

        return $session_id;
    }

    /**
     * @depends testNeonCrmLogin
     */
    public function testNeonCrmQueryForLifeMember(String $session_id) : void
    {
       $result = self::$client->findMember($this->life_member_email);
       $this->assertIsObject($result);
       $this->assertIsObject($result->listAccountsResponse);
       $this->assertEquals('SUCCESS',$result->listAccountsResponse->operationResult);
       $this->assertIsArray($result->listAccountsResponse->searchResults->nameValuePairs);
       $data = $result->listAccountsResponse->searchResults->nameValuePairs;
       $this->assertIsArray($data);
    }

    /**
     * @depends testNeonCrmLogin
     */
    public function testNeonCrmQueryForNormalMember(String $session_id) : void
    {
       $result = self::$client->findMember($this->normal_member_email);
       $this->assertIsObject($result);
       $this->assertIsObject($result->listAccountsResponse);
       $this->assertEquals('SUCCESS',$result->listAccountsResponse->operationResult);
       $this->assertIsArray($result->listAccountsResponse->searchResults->nameValuePairs);
       
    }
    
    /**
     * @depends testNeonCrmLogin
     */
    public function testVerifyMembershipForLifeMember(String $session_id)
    {
        $data = self::$client->verifyMembership($this->life_member_email);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('Last Name',$data);
        $this->assertArrayHasKey('First Name',$data);
        $this->assertArrayHasKey('valid',$data);
        $this->assertTrue($data['valid']);
    }

    /**
     * @depends testNeonCrmLogin
     */
    public function testVerifyMembershipForNormalMember(String $session_id)
    {
        $data = self::$client->verifyMembership($this->normal_member_email);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('Last Name',$data);
        $this->assertArrayHasKey('First Name',$data);
        $this->assertArrayHasKey('valid',$data);
        $this->assertTrue($data['valid']);
    }

    public function testSendDiscourseInvitation()
    {
        $response = self::$client->sendInvitation('mintz@vernontbludgeon.com');
        $this->assertIsObject($response);
        $this->assertIsInt($response->id);
        self::$invitation_id = $response->id;
        dump($response);

    }

    //public function testDeleteInvitation

    // public function tearDown() : void
    // {
    //     $response = self::$client->deleteInvitation(self::$invitation_id);
    //     dump($response);
    // }

    public static function tearDownAfterClass(): void
    {
        $response = self::$client->deleteInvitation(self::$invitation_id);

        //dump($response);
    }

}