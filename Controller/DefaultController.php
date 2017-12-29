<?php

namespace SteveCohen\eZPublishHelpersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SteveCoheneZPublishHelpersBundle:Default:index.html.twig');
    }
}
