<?php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categories = ['Home','Domotic','Nature', 'Entreprise','Physics', 'Maths', 'Animals','Random','Dev','Travel'];

        for($o = 0; $o<10; $o++)
        {
            $category = new Category();
            $category->setName($categories[$o]);
            $manager->persist($category);
            $manager->flush();
            for($i = 0; $i<$o;$i++)
            {
                $article = new Articles();
                $article->setTitle("This is the title of the article ".$i);
                $article->setCategory($category);
                $article->setViews(rand(0, 10000));
                $article->setCreatedAt(new \DateTime());
                $article->setDescription("This is a little description of the article ".$i);
                $article->setThumbnailImg('https://picsum.photos//id/'.$o.$i.$o.'/800/533');
                $article->setBody("Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium animi assumenda aut consequuntur cumque excepturi hic laudantium odio repellendus. Aliquam in iure quae? Alias eaque enim laudantium! Dolore, nulla tempora!".$i);
                $manager->persist($article);
                $manager->flush();
                for($l = 0; $l<$i;$l++){
                    $comment = new Comment();
                    $comment->setArticle($article);
                    $comment->setBody("This is the comment ".$l."of the article ".$i);
                    $comment->setLikes(0);
                    $manager->persist($comment);
                    $manager->flush();
                }
            }
        }

        // $product = new Product();
        // $manager->persist($product);


    }
}
