<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Form\RecetteType;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Form\CommentType;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Form\CategoryType;

use App\Entity\User;
use App\Repository\UserRepository;


class BlogController extends AbstractController
{
    //GESTION DE LA PAGE DE BASE ET DE L'AFFICHAGE D'UNE RECETTE

//fonction qui affiche toute les recettes sur la page principale
    /**
     * @Route("/", name="home")
     */
    public function home(RecetteRepository $repo)
    {
        $repo = $this->getDoctrine()->getRepository(Recette::class);

        $recettes = $repo->findAll();

        return $this->render('blog/home.html.twig', [
            'controller_name' => 'BlogController',
            'recettes' => $recettes
        ]);
    }

//Fonction qui permet l'affichage ciblé d'une recette 
//Permet aussi l'ajout d'un commentaire sur une recette et de lui donné un "datetime" et de le faire persisté dans la base de données  
    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Recette $recette, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime())
                    ->setRecette($recette);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $recette->getId()]);
        }

        return $this->render('blog/show.html.twig', [
            'recette' => $recette,
            'commentForm' => $form->createView()
        ]);
    }

    //CRUD DE L'ENTITY RECETTE

//Fonction qui permet à la fois la création et la modification des recettes du site    
    /**
     * @Route("/admin/recepies/create", name="create")
     * @Route("/admin/recepies/edit/{id}", name="edit")
     */
    public function form(Recette $recette = null, Request $request, EntityManagerInterface $manager)
    {
        //Permet de différencier si la reccette est à créer ou a modifier 
        if(!$recette)
        {
            $recette = new Recette();
        }
        //Appel le formulaire liè à la recette -> permet de ne pas surchargé le controller et de gérer le form à part
        $form = $this->createForm(RecetteType::class, $recette);           

        $form->handleRequest($request);
        //Vérifier que tout est bien conforme au et valide par rapport au contrainte que nous avons posé 
        if($form->isSubmitted() && $form->isValid()) {
            //si c'est une nouvelle recette lui génère un id et un DateTime 
            if(!$recette->getId())
            {
            $recette->setCreatedAt(new \DateTime());
            }
// upload d'une image associer à la recette et qui gère l'ajout à la fois en db et dans un fichier dédié du projet
            $file = $recette->getImage();
            $filename = md5(\uniqid()).'.'.$file->guessExtension();
            try{
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $filename
                );
            } catch (FileException $e) {
            //appel le manager pour (faire persister la recette et l'inclure dans la db)
            }
            $manager = $this->getDoctrine()->getManager();
            //envoie l'image dans la db en lui donnant un nom unique différent du nom d'origine 
            //(permet d'inclure plusieurs fois la meme image sans avoir de soucis)
            $recette->setImage($filename);
            $manager->persist($recette);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $recette->getId()]);
        }

        return $this->render('blog/create.html.twig', [
            'formRecette' => $form->createView(),
            'editMode' => $recette->getId() !== null
        ]);
    }

//Permet la suppression d'un article de la db
    /**
     * @Route("/delete/{id}", name="removeBlogPost", methods={"GET"})
     */
    public function removeBlogPost(Recette $recette, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($recette);
        $em->flush();

        return $this->redirectToRoute("modify");
    }   

//Permet l'affichage de toute les recettes de la db en tableau (PARTIE ADMIN)    
    /**
     * @Route("/admin/modify", name="modify")
     */
    public function modify(RecetteRepository $repo)
    {
        $recette=$repo->findAll();

        return $this->render('blog/modify.html.twig', [
            'recettes' => $recette
        ]);
    }

    //GESTION DE LA PARTIE ADMIN

//Affichage de la page ADMIN qui permet de se rediriger vers toutes les sous parties de l'administration du site    
    /**
     * @Route("/admin", name="admin")
     */
    public function admin()
    {
        return $this->render('blog/admin.html.twig');
    }

//Récupère dans la db tout les utilisateurs inscrits et les affiches dans un tableau
    /**
     * @Route("/admin/user", name="user")
     */
    public function user(UserRepository $repo)
    {
        $user=$repo->findAll();

        return $this->render('blog/user.html.twig', [
            'users' => $user
        ]);
    }

//Permet via un boutton de supprimer les utilisateurs de la db    
    /**
     * @Route("/admin/user/delete/{id}", name="removeUser", methods={"GET"})
     */
    public function removeUser(User $user, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("user");
    } 

//Récupère dans la db tout les commentaires postés, le nom de l'utilisateur et les affiches dans un tableau    
    /**
     * @Route("/admin/comments", name="comment")
     */
    public function comment(RecetteRepository $repo)
    {
        $repo = $this->getDoctrine()->getRepository(comment::class);

        $comment=$repo->findAll();

        return $this->render('/blog/comment.html.twig', [
            'comments' => $comment
        ]);
    }

//Permet via un boutton de supprimer un commentaire de la db     
    /**
     * @Route("/admin/comments/delete/{id}", name="removeComment", methods={"GET"})
     */
    public function removeComment(Comment $comment, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute("comment");
    }

//Récupère dans la db tout les Catégories qui permettent un clasement des plats pour la recherche et les affiches dans un tableau    
    /**
     * @Route("/admin/category", name="category")
     */
    public function category(CategoryRepository $repo)
    {
        $repo = $this->getDoctrine()->getRepository(category::class);

        $category=$repo->findAll();

        return $this->render('/blog/category.html.twig', [
            'categories' => $category
        ]);
    }

//Permet via un formulaire de créer de nouvelle catégorie    
    /**
     * @Route("/admin/category/create", name="createCategory")
     */
    public function formCategory( Request $request, EntityManagerInterface $manager)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);           

        $form->handleRequest($request);
        //vérifie les conditions de création de la catégorie, la fais persister et l'envoie dans la db 
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute("category");
        }
        
        return $this->render('blog/newcategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

//Permet via un boutton de supprimer une catégorie de la db    
    /**
     * @Route("/admin/category/delete/{id}", name="removeCategory", methods={"GET"})
     */
    public function removeCategory(Category $category, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute("category");
    } 

    //GESTION DE LA PAGE PROFIL 

//Permet l'affichage personnalisé d'un profil et les commentaires postées par celui-ci sur une page dédiée.    
    /**
     * @Route("/profil", name="profil")
     */
    public function profil(Request $request, CommentRepository $commentRepo)
    {
        $user = $this->getUser();
        //permet de trouver les commentaires de la db associé à l'id de l'utilisateur et de les afficher sur la page profil
        $userComments = $commentRepo->findAllByUser($user);
        return $this->render('blog/profil.html.twig', [
            'user' => $user,
            'comments'=> $userComments
        ]);
    }
}
