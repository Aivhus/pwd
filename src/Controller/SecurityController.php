<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
//Page d'inscription pour un nouvelle utilisateur    
     /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder) {
        //création d'un nouvelle utilisateurs
        $user = new User();
        //appel le form d'inscription
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        //valide les informations reçues dans le form 
        if($form->isSubmitted() && $form->isValid()) {
            //Encode le mot de passe pour que la données soit cryptées dans la base de donnée
            $hash = $encoder->encodePassword($user, $user->getPassword());
            //Attribue automatiquement le rôle user à tout nouvelle utilisateur du site
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($hash);
            //fais persister le nouveau USER et l'insert dans la base de données
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //gère la page de connexion 
    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(){
        return $this->render('security/login.html.twig');
    }

    //permet la déco d'un USER 
    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() 
    {

    }
}
