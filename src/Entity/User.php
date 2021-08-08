/** simple class representing a prospective user of NAJIT's Discourse site */
declare(strict_types=1);

namespace App\Entity

class User {

    /**
     * @Assert\Email(
     *     message = "'{{ value }}' is not a valid email address"
     * )
     * @Assert\NotBlank(
     *     message = "Email is required"
     * )
     */    
    private $email;

    public function __construct()
    {
        
    }


    /**
     * Get the value of email
     */ 
    public function getEmail() : String
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail(String $email) : User
    {
        $this->email = $email;

        return $this;
    }   
}