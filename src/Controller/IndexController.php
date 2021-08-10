<?php
namespace App\Controller;

use App\Entity\NAJITMember;
use App\Service\Invitation;
// use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\NAJITMemberFormType;
use Symfony\Component\HttpFoundation\Request;


class IndexController extends AbstractController {

    public function __construct(
        private Invitation $service, 
        private bool $verified = false)
    {
        // such convenience!
        // will need to be downgraded for PHP < 8
    }

    /**
     * @Route("/",name="home")
     */
     public function index(Request $request) : Response
     {
        $form = $this->createForm(NAJITMemberFormType::class);
        // $form->handleRequest($request);
        // if ($form->isSubmitted()) {
        //     if ($form->isValid()) {
        //         $valid = 'valid!';
        //     } else {
        //         $valid = 'NOT valid';
        //     }
        //     return $this->json(['valid' => $valid]);
        // } else {

            return $this->render('index.html.twig',['form' =>$form->createView()]); 
        // }
     }

     /**
      * @Route("/invite", name="invite", methods={"POST"})
      */
     public function invite(Request $request, Invitation $service)
     {
        
        
        $user = new NAJITMember();
        $form = $this->createForm(NAJITMemberFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $valid = 'valid!';
                $errors = [];
            } else {
                $valid = 'NOT valid';
                                $messages = [];
                $errors = $form->getErrors(true);
                foreach($errors as $k => $v) {
                    $messages[$k] = $v->getMessage();  //get_class($v);
                }
            }
            return $this->json(['valid'=> $valid, 'errors'=>(string)$form->getErrors(true), 'messages'=>$messages]);
        } else {
            return $this->json(['result' => 'not submitted??']) ;
        }
        
     }
}
