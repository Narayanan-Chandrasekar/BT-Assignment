<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        
        return $this->render('user/signup.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }


    /**
     * @Route("/signup", name="signup")
     */
    public function signup(Request $request, ValidatorInterface $validator)
    {
      //  print_r($request->request->get("email"));exit;

        //validations
        if(!filter_var($request->request->get("email"), FILTER_VALIDATE_EMAIL)) {
            $emailErr["invalidFormat"] = "Invalid email format";
          }

        
          
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->findByEmail($request->request->get("email"));  
        
        if(!$user)
        {
        //check if the user already exists
        $user = new User();
        $user->setEmail($request->request->get("email"));
        $hashedPassword = password_hash($request->request->get("password"), PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        if($request->request->get("trainer"))
        {
            $user->setIsAdmin(true);
        } else {
            $user->setIsAdmin(false);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('user/login.html.twig', [
            
        ]);

       } else {
           $emailErr['inUse'] = "This email is in use";
           return $this->render('user/signup.html.twig', [
            'err' => $emailErr,
        ]);
       }
        
        
        //return new Response('Saved new user with id '.$user->getId());
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request)
    {
        if($request->request->get("email"))
        {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->findBy(["email"=>$request->request->get("email")]);  
       // print_r($user);exit;
        //check if the user exists
        
        if($user)
        {
          $passwordsMatch = $entityManager->getRepository(User::class)->verifyPassword($request->request->get("password"),$user[0]->getPassword());
          if($passwordsMatch)
          {
           
            if(!isset($_SESSION))
            {
                session_start();
            }
            $_SESSION["'".$request->request->get("email")."'"] = true;
            return $this->render('user/home.html.twig', [
                'email' => $request->request->get("email"),
                'id' => $user[0]->getId(),
                'isAdmin' => $user[0]->getIsAdmin()
                
            ]);
        
            
        } else {
            $emailErr['notValid'] = "The password does not match";
            return $this->render('user/login.html.twig', [
             'err' => $emailErr,
         ]);   
          }      
        } else {
           $emailErr['notValid'] = "This email is not in use";
           return $this->render('user/login.html.twig', [
            'err' => $emailErr,
        ]);
       }
     } else {
        return $this->render('user/login.html.twig');
     }
    }

    /**
     * @Route("/home", name="home",  methods={"GET","POST"})
     */
    public function home(Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
            $requestBody = $request->getContent();
            
            return new JsonResponse(Array("data"=> "Welcome Home"));
            
        }

        return new Response("Hi");
    }

    /**
     * @Route("/logout/{id}", name="logout")
     */
    public function logout($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);  
       // print_r($user);exit;
        //check if the user exists
        
        if($user)
        {
        if(isset($_SESSION))
        {
            unset($_SESSION["'".$user->getEmail()."'"]);
            session_destroy();
        }
        //return new JsonResponse(Array('data'=>"You have been logged out"));

        return $this->render('user/signup.html.twig', [
            'controller_name' => 'UserController',
        ]);
        
    }

    } 

}
