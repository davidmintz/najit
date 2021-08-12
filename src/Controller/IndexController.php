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

            if (! $form->isValid()) {
                $valid = false;
                $messages = [];
                $errors = $form->getErrors(true);
                foreach($errors as $k => $v) {
                    // this is absurd. there must be a better way
                    // to return an array of validation error messages
                    // where the key = element name and value = message
                    $message = $v->getMessage();
                    if (stristr($message, 'email')) {
                        $messages['email'] = $message;
                    } elseif ( true or stristr($message, 'CSRF')) {
                        $messages['csrf'] = $message;
                    } else {
                        throw new \Exception('can\'t figure out form element for message: "$message"');
                    }
                    return $this->json(['valid'=> $valid, 'messages'=>$messages]);
                }
            } else { // valid form. give it a shot.
                $valid = true;
                $errors = [];
                $response = ['valid' => true,'debug' => 'next step, check membership'];
                $data = $service->verifyMembership($user->getEmail());
                $response['member'] = $data;
                return $this->json($response);
            }
        } else {
            return $this->json(['result' => 'not submitted??']) ;
        }
    }
}
