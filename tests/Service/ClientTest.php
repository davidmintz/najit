<?php declare(strict_types=1);

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Service\Invitation;
use PhpParser\Node\Expr\Cast\Array_;

final class ClientTest extends KernelTestCase
{
    
    private  static $client;

    public static function setUpBeforeClass() : void
    {
        $container = static::getContainer();
        self::$client = $container->get(Invitation::class);
    }

    public function setUp() : void
    {
        // fwrite(STDOUT, __METHOD__ . "\n");
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        //$container = static::getContainer();
        //$this->client = $container->get(Invitation::class);
    }

    public function testClientObject()
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
    public function testNeonCrmMembershipQueryForLifeMember(String $session_id)
    {
       $result = self::$client->findMember('david@davidmintz.org');
       $this->assertIsObject($result);
       $this->assertIsObject($result->listAccountsResponse);
       $this->assertEquals('SUCCESS',$result->listAccountsResponse->operationResult);
       $this->assertIsArray($result->listAccountsResponse->searchResults->nameValuePairs);
       $data = $result->listAccountsResponse->searchResults->nameValuePairs;
       dump($data);
    }

    

    /**
     * @depends testNeonCrmLogin
     */
    public function testNeonCrmMembershipQueryForNormalMember(String $session_id)
    {
       $result = self::$client->findMember('info@amirshahilaw.com');
       $this->assertIsObject($result);
       $this->assertIsObject($result->listAccountsResponse);
       $this->assertEquals('SUCCESS',$result->listAccountsResponse->operationResult);
       $this->assertIsArray($result->listAccountsResponse->searchResults->nameValuePairs);
       $data = $result->listAccountsResponse->searchResults->nameValuePairs;
       
    }
    
}