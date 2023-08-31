<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function PHPUnit\Framework\isEmpty;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN') == false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

        return $this->render('admin/index.html.twig', []);
    }

    #[Route('/Denied', name: 'app_admin_denied')]
    public function denied(): Response
    {

                return $this->render('admin/Access_Denied.html.twig', []);

    }

    #[Route('/Users', name: 'app_admin_allusers')]
    public function AllUsers(UserRepository $userRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') == false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

        return $this->render('admin/AllUsers.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }


    #[Route('/add', name: 'app_register_admin')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ADMIN') == false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

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
                $user->setImage("NoImage.png");
            }
            $user->setRoles([
                'ROLE_ADMIN'
            ]);
            $user->setIsVerified(1);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash(
                'info',
                'ADMIN ADDED.'
            );

            return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route("/{id}/user", name:"app_user_delete")]
    public function delete(EntityManagerInterface $entityManager,ManagerRegistry $doctrine,UserRepository $userRepository, $id)
    {

        $user = $userRepository->find($id);

        $entityyManager = $doctrine->getManager();
        $entityyManager->remove($user);
        $entityyManager->flush();

        $this->addFlash(
            'info-delete',
            'Deleted Successfully'
        );


        return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/edit', name: 'app_user_edit_etat', methods: ['GET', 'POST'])]
    public function editEtat($id,UserRepository $userRepository ): Response
    {
        $user = $userRepository->getUserById($id);
        if($user->isVerified() == 0) {
            $user->setIsVerified(1);
        }
        else
            $user->setIsVerified(0);
        //update user
        $userRepository->updateUser( $user, true);

        return $this->redirectToRoute('app_admin_allusers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/editAdmin', name: 'app_user_edit_admin', methods: ['GET', 'POST'])]
    public function editAdmin(UserRepository $userRepository ): Response
    {
        if ($this->isGranted('ROLE_ADMIN') == false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

        $user = $userRepository->getUserById($this->getUser()->getId());
        if($user->isVerified() == 0) {
            $user->setIsVerified(1);
        }
        else
            $user->setIsVerified(0);
        //update user
        //$userRepository->updateUser( $user, true);

        return $this->render('admin/EditAdmin.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/EditProfileAdmin', name: 'app_edit_profile_admin', methods: ['GET', 'POST'])]
    public function EditProfile(Request $request,UserRepository $userRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') == false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

        $user = $this->getUser();
        $user->setName($request->get("name"));
        $user->setEmail($request->get("email"));
        $rest=substr($request->get('bday'), 0, 20);
        $rest1=substr($request->get('bday'), 30, 34);
        $res=$rest.$rest1;
        try {
            $date = new \DateTime($res);
            $user->setBday($date);
        } catch (\Exception $e) {

        }

        $file = $request->files->get('image');
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
        }

        $userRepository->updateUser($user, true);

        return $this->render('admin/EditAdmin.html.twig', []);
    }

    #[Route('/EditAdminPassword', name: 'app_edit_admin_password', methods: ['GET', 'POST'])]
    public function EditAdminPassword(Request $request,UserPasswordHasherInterface $userPasswordHasher,UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if($request->get("newpass")=="" && $request->get("confirmpass")=="")
        {
            $this->addFlash(
                'info-warning',
                'Password empty.'
            );
            return $this->render('admin/EditAdmin.html.twig', [
                'user' => $user,
            ]);
        }
        else
        {
            if($request->get("newpass")==$request->get("confirmpass"))
            {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->get("newpass")
                    )
                );

                $userRepository->updateUserPassword($user, true);
                $this->addFlash(
                    'info-success',
                    'Password changed with success.'
                );

                return $this->render('admin/EditAdmin.html.twig', [
                    'user' => $user,
                ]);
            }
            else
            {
                $this->addFlash(
                    'info-warning',
                    'Password dont match.'
                );
            }

            return $this->render('admin/EditAdmin.html.twig', [
                'user' => $user,
            ]);
        }

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
