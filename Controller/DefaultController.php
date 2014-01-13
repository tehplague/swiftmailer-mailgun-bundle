<?php

namespace cspoo\Swiftmailer\MailgunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('cspooSwiftmailerMailgunBundle:Default:index.html.twig', array('name' => $name));
    }
}
