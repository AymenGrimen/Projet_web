<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        if($this->getUser())
        {
            if (array_values($this->getUser()->getRoles())[0]=="ROLE_ADMIN") {
                return $this->render('admin/index.html.twig', [
                    'controller_name' => 'AdminController',
                ]);
            }

        }
        return $this->redirectToRoute('app_admin_denied');

    }
    #[Route('/Denied', name: 'app_admin_denied')]
    public function denied(): Response
    {

                return $this->render('admin/Access_Denied.html.twig', []);

    }

    #[Route('/Users', name: 'app_admin_allusers')]
    public function AllUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/AllUsers.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }

    #[Route(path: '/login', name: 'app_login_admin')]
    public function loginAdmin(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser())
        {
            if (array_values($this->getUser()->getRoles())[0]=="ROLE_ADMIN") {
                return $this->redirectToRoute('app_admin');
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }



    #[Route('/add', name: 'app_register_admin')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImage($fileName);
            } else {
                $user->setImage("NoImage.svg");
            }
            $user->setRoles([
                'ROLE_ADMIN'
            ]);
            $user->setEtat(0);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/{id}/user', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_user_edit_etat', methods: ['GET', 'POST'])]
    public function editEtat($id,UserRepository $userRepository ): Response
    {
        $user = $userRepository->getUserById($id);
            if($user->getEtat() == 0) {
                $user->setEtat(1);
            }
            else
                $user->setEtat(0);
            //update user
            $userRepository->updateUser( $user, true);

            return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/r/search_user', name: 'search_user', methods: ['GET'])]
    public function search_user(Request $request, NormalizerInterface $Normalizer, UserRepository $userRepository): Response
    {

        $requestString = $request->get('searchValue');
        $requestString3 = $request->get('orderid');

        $user = $userRepository->findUser($requestString, $requestString3);
        $jsoncontentc = $Normalizer->normalize($user, 'json', ['users' => 'posts:read']);
        $jsonc = json_encode($jsoncontentc);
        if ($jsonc == "[]") {
            return new Response(null);
        } else {
            return new Response($jsonc);
        }
    }

}
