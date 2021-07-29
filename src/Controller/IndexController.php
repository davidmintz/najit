<?php
namespace App\Controller;

use App\Service\Invitation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController {

    /**
     * @Route "/"
     */
     public function index(Invitation $invitation) : Response
     {
        //dump($invitation->getConfig());
        //dump($invitation->login());
      $shit = $invitation
         ->verifyMembership('info@amirshahilaw.com');
         //->verifyMembership('david@davidmintz.org');
         //->listAccountsResponse;// ->searchResults;//->nameValuePairs;
      // $t = '';
      // foreach ($shit as $s) {
      //    $t .= print_r($s,true);
      // }
        return new Response(sprintf("<html><body><pre>%s</pre></body></html>","Woo hoo!<br>".print_r($shit,true)));
     }

}
;