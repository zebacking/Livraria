<?php

namespace LivrariaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="painel")
     */
    public function indexAction()
    {
        return $this->render('LivrariaBundle:Default:index.html.twig');
    }
}
