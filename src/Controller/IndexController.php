<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Length;

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
        if ($this->isGranted('ROLE_ADMIN') != false)
        {
            return $this->redirectToRoute('app_admin_denied');
        }

        return $this->render('index/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }


    #[Route('/EditProfilePassword', name: 'app_edit_profile_password', methods: ['GET', 'POST'])]
    public function EditProfilePassword(Request $request,UserPasswordHasherInterface $userPasswordHasher,UserRepository $userRepository): Response
    {

        $user = $this->getUser();
        if($request->get("newpass")=="" && $request->get("confirmpass")=="")
        {
            $this->addFlash(
                'info-warning',
                'Password empty.'
            );
            return $this->render('index/profile.html.twig', [
                'user' => $user,
            ]);
        }
        else
        {
            if(strlen($request->get("newpass"))<6)
            {
                $this->addFlash(
                    'info-warning',
                    'Your password should be at least 6 characters.'
                );
                return $this->render('index/profile.html.twig', [
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
    
                    return $this->render('index/profile.html.twig', [
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
    
                return $this->render('index/profile.html.twig', [
                    'user' => $user,
                ]);
    
            }
        }

    }

    #[Route('/EditProfile', name: 'app_edit_profile', methods: ['GET', 'POST'])]
    public function EditProfile(Request $request,UserRepository $userRepository): Response
    {

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
        $this->addFlash(
            'info-success-info',
            'Information changed with success.'
        );

        return $this->render('index/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/verification/from/JavaMail', name: 'app_verify_email_java')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->getUserByEmail($request->get('email'));
        $user->setIsVerified(1);
        $userRepository->updateUser( $user, true);
        return $this->render('index/EmailConfirmed.html.twig');

    }

    #[Route('/findusermobEmail', name: 'mobfinduser')]
    public function mobfinduser(Request $request,NormalizerInterface $Normalizer,UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy([
            'email' => $request->get('email')
        ]);

        $jsoncontentc = $Normalizer->normalize($user, 'json', ['users' => 'posts:read']);
        $jsonc = json_encode($jsoncontentc);
        if ($user) {
            return new Response($jsonc);
        } else {
            return new Response("[]");
        }
    }
    #[Route('/mobile/login', name: 'loginmob')]
    public function loginmob(Request $request,NormalizerInterface $Normalizer,UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy([
            'email' => $request->get('email')
        ]);

        $jsoncontentc = $Normalizer->normalize($user, 'json', ['users' => 'posts:read']);
        $jsonc = json_encode($jsoncontentc);
        if ($user) {
            return new Response($jsonc);
        } else {
            return new Response("[]");
        }
    }

    #[Route('/mobile/new', name: 'addmobuser')]
    public function addmobuser(Request $request,NormalizerInterface $normalizer,EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher)
    {
        $user= new User();
        $user->setName($request->get('name'));
        $user->setEmail($request->get('email'));

        $rest=substr($request->get('bday'), 0, 20);
        $rest1=substr($request->get('bday'), 30, 34);
        $res=$rest.$rest1;
        try {
            $date = new \DateTime($res);
            $user->setBday($date);
        } catch (\Exception $e) {

        }
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get('password')
            )
        );
        $user->setImage($request->get('image'));
        if($request->get('role') != "[]")
        $user->setRoles([
            'ROLE_ADMIN'
        ]);
        $user->setIsVerified($request->get('isVerified'));
        $entityManager->persist($user);
        $entityManager->flush();

        $jsonContent = $normalizer->normalize($user,'json',['user'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    #[Route('/user/mobile/edit', name: 'editmobuser')]
    public function editmobuser(Request $request,NormalizerInterface $normalizer,UserRepository $userRepository ,UserPasswordHasherInterface $userPasswordHasher)
    {
        $user= $userRepository->find($request->get('id'));

        $user->setName($request->get('name'));
        $user->setEmail($request->get('email'));

        $rest=substr($request->get('bday'), 0, 20);
        $rest1=substr($request->get('bday'), 30, 34);
        $res=$rest.$rest1;
        try {
            $date = new \DateTime($res);
            $user->setBday($date);
        } catch (\Exception $e) {

        }
        $user->setImage($request->get('image'));


        $userRepository->updateUser($user, true);

        $jsonContent = $normalizer->normalize($user,'json',['user'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    #[Route('/user/mobile/editpassword', name: 'editpasswordmobuser')]
    public function editpasswordmobuser(Request $request,NormalizerInterface $normalizer,UserRepository $userRepository ,UserPasswordHasherInterface $userPasswordHasher)
    {
        $user= $userRepository->find($request->get('id'));
    
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get('password')
            )
        );

        $userRepository->updateUserPassword($user, true);

        $jsonContent = $normalizer->normalize($user,'json',['user'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    #[Route('/User/del', name: 'deleteusermobile')]
    public function deleteusermobile(Request $request,NormalizerInterface $normalizer,UserRepository $userRepository,EntityManagerInterface $entityManager )
    {
        $user= $userRepository->find($request->get('id'));
    
        $entityManager->remove($user);
        $entityManager->flush();
        
        $jsonContent = $normalizer->normalize($user,'json',['user'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    
}
