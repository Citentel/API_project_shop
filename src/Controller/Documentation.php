<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class Documentation extends AbstractController
{
    private KernelInterface $appKernel;

    public function __construct
    (
        KernelInterface $appKernel
    )
    {
        $this->appKernel = $appKernel;
    }

    /**
     * @Route("/")
     */
    public function renderDocumentation(): Response
    {
        $config = file_get_contents($this->appKernel->getProjectDir() . '/public/json/documentation.json');
        $config = json_decode($config, true);

        return $this->render('documentation.html.twig', [
            'config' => $config,
        ]);
    }
}