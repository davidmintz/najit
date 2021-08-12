<?php
/** simple class representing a prospective user of NAJIT's Discourse site */

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class NAJITMember
{

    /**
     * @var string
     * 
     * @Assert\Email(
     *     message = "'{{ value }}' is not a valid email address"
     * )
     * @Assert\NotBlank(
     *     message = "Email is required"
     * )
     */    
    private $email;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     * 
     * maybe change it to DateTime?
     */
    private $expiration_date;

    public function __construct()
    {
        
    }


    /**
     * Get the value of email
     */ 
    public function getEmail() :? String
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail(String $email) : NAJITMember
    {
        $this->email = $email;

        return $this;
    }   

    /**
     * Get the value of expiration_date
     */ 
    public function getExpiration_date() :? String
    {
        return $this->expiration_date;
    }

    /**
     * Set the value of expiration_date
     *
     * @return  self
     */ 
    public function setExpiration_date($expiration_date)  : NAJITMember
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }

    /**
     * Get the value of firstname
     */ 
    public function getFirstname() :? String
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */ 
    public function setFirstname($firstname) : NAJITMember
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */ 
    public function getLastname() :? String
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */ 
    public function setLastname($lastname) : NAJITMember
    {
        $this->lastname = $lastname;

        return $this;
    }
}