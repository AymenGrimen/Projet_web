<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if($this->getUser())
        {
            if (array_values($this->getUser()->getRoles())[0]=="ROLE_USER") {
                return $this->redirectToRoute('app_index');
            }

        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();



        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'message' => '']);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $logoutUrl = $urlGenerator->generate('app_login');

        return new RedirectResponse($logoutUrl);    }

}
