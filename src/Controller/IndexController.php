<?php
namespace App\Controller;

use App\Service\Invitation;
// use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController {

    public function __construct(
        private Invitation $service, 
        private bool $verified = false)
    {
        // such convenience!
    }

    /**
     * @Route("/",name="home")
     */
     public function index(Invitation $service) : Response
     {
      
         return $this->render('index.html.twig'); 
     }

     /**
      * @Route("/verify/{email}", name="verify")
      */
     public function verifyMembership($email)
     {
        $shit = get_class($this->service) ;
        return $this->json(['email' => $email,'shit'=>$shit]);
     }



}
