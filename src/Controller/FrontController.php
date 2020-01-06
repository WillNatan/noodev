<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Ips;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\FrontUserEditType;
use App\Form\PasswordEditType;
use App\Form\RegisterType;
use App\Form\UserEditType;
use App\Form\UserPasswordEditType;
use App\Form\UserType;
use App\Repository\ArticlesRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\IpsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\Json;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="blogspot")
     */
    public function index(Request $request, ArticlesRepository $articlesRepository, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($newPassword);
            $user->setDescription('Aucune description');
            $user->setAvatar('default.png');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }
        $latestArticles = $articlesRepository->findBy([],['id'=>'DESC'],'6');
        return $this->render('front/index.html.twig',
            [
                'latestArticles'=>$latestArticles,
                'form'=>$form->createView()
            ]);
    }


    /**
     * @Route("/profil", name="blogspot_profile")
     */
    public function profil(Request $request, ArticlesRepository $articlesRepository, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        return $this->render('front/profile.html.twig', ['user'=>$user]);
    }

    /**
     * @Route("/modifier-vos-informations", name="blogspot_profile_edit")
     */
    public function infoprofil(Request $request, ArticlesRepository $articlesRepository, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        $user =$this->getUser();
        $oldAvatar = $user->getAvatar();
        $form = $this->createForm(FrontUserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $user->getAvatar();
            if($avatar)
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
            else{
                $currentUser = $userRepository->findOneBy(['id'=>$user->getId()]);
                $currentUser->setAvatar($oldAvatar);
            }
            /*
             * if($encoder->isPasswordValid($user, $oldPassword)){
                $newPassword=$encoder->encodePassword($user,$user->getPlainPassword());
                $user->setPassword($newPassword);
            }
             */
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('blogspot_profile');
        }
        return $this->render('front/editprofile.html.twig', ['form'=>$form->createView(), 'user'=>$user]);
    }

    /**
     * @Route("/modifier-votre-mot-de-passe", name="blogspot_profile_edit_password")
     */
    public function passwordedit(Request $request, ArticlesRepository $articlesRepository, UserPasswordEncoderInterface $encoder)
    {

        $user = $this->getUser();
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
        return $this->render('front/password.html.twig', ['form'=>$form->createView(), 'user'=>$user]);
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function register(Request $request, ArticlesRepository $articlesRepository, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($newPassword);
            $user->setIpAddress($request->getClientIp());
            $user->setDescription('Aucune description');
            $user->setAvatar('default.png');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }
        return $this->render('front/signup.html.twig',
            [
                'form'=>$form->createView()
            ]);
    }

    /**
     * @Route("/add-view", name="add_view", methods={"POST", "GET"})
     */
    public function addView(Request $request, ArticlesRepository $articlesRepository){
        if($request->isMethod('POST')){
            $articleView = $request->request->get('articleId');
            $article = $articlesRepository->findOneBy(['id'=>$articleView]);
            $article->setViews($article->getViews()+1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
        }else{
            return $this->redirectToRoute('blogspot');
        }
    }

    /**
     * @Route("/recherche", name="search-article", methods={"POST", "GET"})
     */
    public function searchArticle(Request $request, ArticlesRepository $articlesRepository){
        if($request->isMethod('POST')){
           $articles = $articlesRepository->createQueryBuilder('t')
               ->select('t.id','t.title','t.description','t.thumbnail_img', 't.slug', 't.views', 't.created_at')
               ->leftJoin('t.Category','c')
               ->addSelect('c.name')
               ->where('t.title LIKE :search')
               ->setParameter('search','%'.$request->get('search-value').'%')
               ->getQuery()
               ->getResult();

           return $this->render('front/foundArticles.html.twig',
               [
                   'articles'=>$articles
               ]);
        }else{
            return $this->redirectToRoute('blogspot');
        }
    }

    /**
     * @Route("/blog/{slug}", name="single_blog", methods={"POST", "GET"})
     */
    public function singleBlog(Articles $articles,CommentRepository $commentRepository, ArticlesRepository $articlesRepository, UserRepository $userRepository,Request $request): Response
    {

        $relatedPosts = $articlesRepository->findBy(['Category'=>$articles->getCategory()],['id'=>'ASC'],'3');
        $user = $this->getUser();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setArticle($articles);
            $comment->setLikes(0);
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été rajouté !');
            return $this->redirectToRoute('single_blog',['slug'=>$articles->getSlug()]);
        }
        return $this->render('front/single.html.twig', ['relatedPosts'=>$relatedPosts,
            'article' => $articles,
            'form' => $form->createView(),
            'user'=>$user,
        ]);
    }

    /**
     * @Route("/demande-reinitialisation-du-mot-de-passe", name="reset-password", methods={"POST", "GET"})
     */
    public function resetPassword(Request $request,UserRepository $userRepository ,TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer): Response
    {
        if($request->isMethod('POST'))
        {
            $email = $request->request->get('email');
            $em = $this->getDoctrine()->getManager();
            $user = $userRepository->findOneBy(['email'=>$email]);

            if($user === null)
            {
                $this->addFlash('danger','Email non reconnu.');
                return $this->redirectToRoute('reset-password');
            }
            $token = $tokenGenerator->generateToken();

            try
            {
                $user->setToken($token);
                $em->flush();
            }catch(\Exception $e){
                $this->addFlash('warning',$e->getMessage());
                return $this->redirectToRoute('reset-password');
            }

            $url = $this->generateUrl('reset-password-token', ['tokenpassword'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Réinitialisation du mot de passe Noodev'))
                ->setFrom('messagerie@wnatan.fr')
                ->setTo($user->getEmail())
                ->setBody(
                    $user->getUsername().", afin de réinitialiser votre mot de passe, veuillez cliquer sur ce lien : " . $url,
                    'text/html'
                );


            $this->addFlash('notice', 'Un mail vous a été envoyé avec un lien de réinitialisation du mot de passe');

            $mailer->send($message);

            return $this->redirectToRoute('blogspot');
        }

        return $this->render('front/forgottenPassword.html.twig');
    }

    /**
     * @Route("/reinitialisation-du-mot-de-passe/{tokenpassword}", name="reset-password-token", methods={"POST", "GET"})
     */
    public function passwordResetting(Request $request,UserRepository $userRepository, string $tokenpassword, UserPasswordEncoderInterface $encoder): Response
    {
        if($request->isMethod('POST'))
        {

            $user = $userRepository->findOneBy(['token'=>$tokenpassword]);

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('reset-password-token',['tokenpassword'=>$tokenpassword]);
            }

            $user->setToken(null);
            if($request->request->get('plainPassword') == $request->request->get('password')){
                $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }

            $this->addFlash('notice', 'Mot de passe mis à jour');

            return $this->redirectToRoute('blogspot');
        }else {

            return $this->render('front/resetPasswordToken.html.twig', ['token' => $tokenpassword]);
        }
    }


    /**
     * @Route("/categorie/{name}", name="blogs_category")
     */
    public function postsByCategory(Category $category, ArticlesRepository $articlesRepository){
        return $this->render('front/posts.html.twig', ['cateogry'=>$category,'articles'=>$articlesRepository->findBy(['Category'=>$category]),
        ]);
    }


    /**
     * @Route("/tous-les-articles", name="allArticles")
     */
    public function allArticles(ArticlesRepository $articlesRepository)
    {
        $allarticles = $articlesRepository->findAll();
        return $this->render('front/all.html.twig', ['allArticles'=>$allarticles]);
    }
}
