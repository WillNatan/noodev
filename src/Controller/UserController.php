<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordEditType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("adminDashboard/utilisateurs")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/nouveau", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $user->getAvatar();

            if($avatar)
            {
                $newfn = $avatar->getClientOriginalName();
                $fs = new Filesystem();
                if($fs->exists($this->getParameter('avatars').'/'.$avatar)){
                    $user->setAvatar($newfn);
                }
                else{
                    try {
                        $avatar->move(
                            $this->getParameter('avatars'),
                            $newfn
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user->setAvatar($newfn);
                }
            }

            $newPassword = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($newPassword);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        if($this->getUser() === $user){
           return $this->redirectToRoute('adminDashboard');
        }
        $oldImg = $user->getAvatar();
        $oldPassword = $user->getPassword();
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $user->getAvatar();

            if($avatar && $avatar !=$oldImg)
            {
                $newfn = $avatar->getClientOriginalName();
                    try {
                        $avatar->move(
                            $this->getParameter('avatars'),
                            $newfn
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user->setAvatar($newfn);
            }
            /*
             * if($encoder->isPasswordValid($user, $oldPassword)){
                $newPassword=$encoder->encodePassword($user,$user->getPlainPassword());
                $user->setPassword($newPassword);
            }
             */
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'lastImg'=>$oldImg
        ]);
    }

    /**
     * @Route("/{id}/modifier-mot-de-passe", name="user_edit_password", methods={"GET","POST"})
     */
    public function editPw(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        if($this->getUser() === $user){
            return $this->redirectToRoute('adminDashboard');
        }
        $form = $this->createForm(PasswordEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($newPassword);
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
