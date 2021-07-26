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
        dump($invitation->getConfig());
        return new Response(sprintf("<html><body>%s</body></html>","Hello fuckin' world. We instantiated a ".get_class($invitation)));
     }

}