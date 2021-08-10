<?php
namespace App\Controller;

use App\Service\Invitation;
// use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\NAJITMemberFormType;

class IndexController extends AbstractController {

    public function __construct(
        private Invitation $service, 
        private bool $verified = false)
    {
        // such convenience!
        // may need to be downgraded for PHP < 8
    }

    /**
     * @Route("/",name="home")
     */
     public function index(Invitation $service) : Response
     {
        $form = $this->createForm(NAJITMemberFormType::class);
        return $this->render('index.html.twig',['form' =>$form->createView()]); 
     }

     /**
      * @Route("/invite", name="invite", methods={"POST"})
      */
     public function invite()
     {
        $shit = get_class($this->service) ;
        return $this->json(['email' => 'gack','shit'=>$shit]);
     }

     



}
