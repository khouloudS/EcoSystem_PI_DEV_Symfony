<?php

namespace EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Event/Default/index.html.twig');
    }

    public function indexadminAction()
    {
        return $this->render('@Event/Default/indexadmin.html.twig');
    }
}
