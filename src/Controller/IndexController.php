<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {

        return $this->render('index/index.html.twig', []);
    }
    #[Route('/MyProfile', name: 'app_myprofile')]
    public function Myprofile(): Response
    {

        return $this->render('index/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/EditProfile', name: 'app_edit_profile', methods: ['GET', 'POST'])]
    public function EditProfile(Request $request,UserRepository $userRepository): Response
    {

        $user = $userRepository->findOneBy([
            'email' => $this->getUser()->getEmail()
        ]);
        $user->setName($request->get("username"));
        $user->setEmail($request->get("email"));

        $userRepository->updateUser($user, true);

        return $this->render('index/profile.html.twig', [
            'user' => $user,
        ]);
    }

}
