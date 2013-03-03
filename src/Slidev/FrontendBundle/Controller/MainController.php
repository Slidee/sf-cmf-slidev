<?php

namespace Slidev\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function dynamicAction()
    {
        return $this->render('SlidevFrontendBundle:Main:dynamic.html.twig');
    }

    public function staticAction()
    {
        return $this->render('SlidevFrontendBundle:Main:static.html.twig');
    }
}