<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\PasswordEditType;
use App\Form\UserEditType;
use App\Form\UserPasswordEditType;
use App\Repository\ArticlesRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/adminDashboard")
 */
class BackController extends AbstractController
{
    /**
     * @Route("/", name="adminDashboard")
     */
    public function index(Request $request)
    {

        return $this->render('back/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }


    /**
     * @Route("/profil/{id}", name="profile_index")
     */
    public function profile(User $user)
    {
        return $this->render('back/profile.html.twig', ['user'=>$user]);
    }

    /**
     * @Route("/commentaires", name="comments")
     */
    public function commentaires(ArticlesRepository $articlesRepository)
    {
        return $this->render('back/comments.html.twig', ['articles'=>$articlesRepository->findAll()]);
    }

    /**
     * @Route("/{id}", name="remove_comment", methods={"DELETE"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comments');
    }

    /**
     * @Route("/profil/{id}/modifier", name="profile_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
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
            }else{
                $user->setAvatar($oldImg);
            }
            /*
             * if($encoder->isPasswordValid($user, $oldPassword)){
                $newPassword=$encoder->encodePassword($user,$user->getPlainPassword());
                $user->setPassword($newPassword);
            }
             */
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('profile_index', ['id'=>$user->getId()]);
        }

        return $this->render('back/profileEdit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'lastImg'=>$oldImg
        ]);
    }

    /**
     * @Route("profil/{id}/modifier-mot-de-passe", name="profile_edit_password", methods={"GET","POST"})
     */
    public function editPw(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserPasswordEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPw = $request->request->get('user_password_edit')['oldPassword'];
            if($encoder->isPasswordValid($user, $oldPw)){
                $newPassword = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($newPassword);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('app_logout');
            }
        }

        return $this->render('back/password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

}
