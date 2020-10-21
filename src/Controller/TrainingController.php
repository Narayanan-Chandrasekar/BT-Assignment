<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Training;
use App\Entity\User;
use App\Entity\Reservation;
class TrainingController extends AbstractController
{
    /**
     * @Route("/training/{id}", name="training")
     */
    public function index($id, Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
            
                $entityManager = $this->getDoctrine()->getManager();
                $repository = $this->getDoctrine()->getRepository(User::class);
                $userId =  $repository->find($id);
                $trainings = $entityManager->getRepository(Training::class)->findBy(["userId"=>$userId]); 
                 
                //print_r($trainings);exit;
                if($trainings)
                {
                    foreach ($trainings as $training){

                        $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                        $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                    }
                    
                    return new JsonResponse($output);
                } else {
                    return new JsonResponse(Array("data"=> "No training modules added yet."));
                }
            
        }

        
    /*    return $this->render('training/index.html.twig', [
            'controller_name' => 'TrainingController',
        ]);
    */ }

    /**
     * @Route("/viewTraining/{id}", name="viewTraining")
     */
    public function view($id, Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
            
                $entityManager = $this->getDoctrine()->getManager();
                $trainings = $entityManager->getRepository(Training::class)->findById($id); 
                 
                //print_r($trainings);exit;
                if($trainings)
                {
                    foreach ($trainings as $training){

                        $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                        $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                    }
                    return new JsonResponse($output);
                } else {
                    return new JsonResponse(Array("data"=> "No info on the training module added yet."));
                }
            
        }

        
    /*    return $this->render('training/index.html.twig', [
            'controller_name' => 'TrainingController',
        ]);
    */ }

    /**
     * @Route("/editTraining/{id}", name="editTraining")
     */
    public function edit($id, Request $request)
    { 
      
        if ($request->isMethod('POST'))
        {
            $requestBody = $request->request->get('data');
            
            $params = Array();
            parse_str($requestBody, $params);
            //print_r($params);exit;
            $entityManager = $this->getDoctrine()->getManager();
            $trainingRepository = $this->getDoctrine()->getRepository(Training::class);
            $trainingModule = $trainingRepository->find($params["trainingId"]);
            //$userRepository = $this->getDoctrine()->getRepository(User::class);
            //$userId =  $repository->find($id);
            //$trainingModule->setUserId($userId);
            $trainingModule->setTopic($params["topic"]);
            $trainingModule->setDescription($params["description"]);
            $trainingModule->setStart(new \DateTime($params["start"]));
            $trainingModule->setEnd(new \DateTime($params["end"]));
            $trainingModule->setDuration($params["duration"]);
            $trainingModule->setSeats($params["seats"]);
        
            $entityManager->persist($trainingModule);
            $entityManager->flush();
                
            return new JsonResponse(Array("data"=> "Training details have been edited."));
        
        
        
        }    
     }

     /**
     * @Route("/deleteTraining/{id}", name="deleteTraining")
     *
     */
    public function delete($id, Request $request)
    { 
      
        if ($request->isMethod('DELETE'))
        {
            $entityManager = $this->getDoctrine()->getManager();
            $trainingRepository = $this->getDoctrine()->getRepository(Training::class);
            $trainingModule = $trainingRepository->find($id);
            //var_dump($trainingModule);exit;
            $areSeatsBooked = $trainingModule->getSeatsBooked();
            if($areSeatsBooked)
            {
                return new JsonResponse(Array("data"=> "Seats are booked against this.Cannot cancel."));
            } else {
                $entityManager->remove($trainingModule);
                
                $entityManager->flush();
                return new JsonResponse(Array("data"=> "Training is cancelled."));
            }
        }    
     }

     /**
     * @Route("/searchTraining/{id}", name="searchTraining")
     *
     */
    public function searchTraining($id, Request $request)
    { 
      
        if ($request->isMethod('POST'))
        {
            $requestBody = $request->request->get('q');
            
            
            $trainingRepository = $this->getDoctrine()->getRepository(Training::class);
            $trainings = $trainingRepository->findByTopic($id, $requestBody);
            if($trainings)
                {
                    foreach ($trainings as $training){

                        $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                        $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                    }
                    return new JsonResponse($output);
                } else {
                    return new JsonResponse(Array("data"=> "No info on the training module added yet."));
                }
            
            
        }    
     }

     

     /**
     * @Route("/searchAllTraining/{id}", name="searchAllTraining")
     *
     */
    public function searchAllTraining($id, Request $request)
    { 
      
        if ($request->isMethod('POST'))
        {
            $requestBody = $request->request->get('q');
            $output = Array();
            
            $trainingRepository = $this->getDoctrine()->getRepository(Training::class);
            $trainings = $trainingRepository->findByTopic("", $requestBody);
            $reservationRepository = $this->getDoctrine()->getRepository(Reservation::class);
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepository->find($id);
            if($trainings)
                {

                    foreach ($trainings as $training){
                        $isTrainingBooked  = $reservationRepository->findOneBy(["userId"=>$user,"trainingId"=> $training]);
                        if(!$isTrainingBooked)
                        {
                            $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                        $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                        }
                        
                    }
                    return new JsonResponse($output);
                } else {
                    return new JsonResponse(Array("data"=> "No info on the training module added yet."));
                }
            
            
        }    
     }

    /**
     * @Route("/reservation/{id}", name="reservation")
     */
    public function reservation($id, Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
            
                $entityManager = $this->getDoctrine()->getManager();
                $output = Array();
                $output1 = Array();
                $output2 = Array();
                
                $reservationRepository = $entityManager->getRepository(Reservation::class);
                $userRepository = $entityManager->getRepository(User::class);
                $trainingRepository = $entityManager->getRepository(Training::class);
                $trainings = $trainingRepository->findByUserId($id);  
                foreach($trainings as $training)
                {
                    $reservations = $reservationRepository->findBy(["trainingId" => $training->getId()]);
                    foreach($reservations as $reservation)
                    {
                        $user = $userRepository->find($reservation->getUserId());
                            //$output[] = $user->getEmail();
                        
                        $topic = $trainingRepository->find($reservation->getTrainingId());
                        
                        $output[] = [$user->getEmail(), $topic->getTopic()];
                        
                        
                    }
                   // $output = array_merge($output1, $output2);
                }
                //print_r($output);exit;
                if($output)
                {
                    return new JsonResponse($output);
                } else {
                    return new JsonResponse(Array("data"=> "No reservations made yet."));
                }
            
        }

        
    /*    return $this->render('training/index.html.twig', [
            'controller_name' => 'TrainingController',
        ]);
    */ }

    /**
     * @Route("/addTraining/{id}", name="addTraining")
     */
    public function addTraining($id, Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
          //  $requestBody = $request->getContent();
           // print_r($requestBody);
          
        }
        if ($request->isMethod('POST'))
        {
            $requestBody = $request->request->get('data');
            
            $params = Array();
            parse_str($requestBody, $params);
            //print_r($params);exit;
            $entityManager = $this->getDoctrine()->getManager();
            
            $trainingModule = new Training();
            $repository = $this->getDoctrine()->getRepository(User::class);
            $userId =  $repository->find($id);
            $trainingModule->setUserId($userId);
            $trainingModule->setTopic($params["topic"]);
            $trainingModule->setDescription($params["description"]);
            $trainingModule->setStart(new \DateTime($params["start"]));
            $trainingModule->setEnd(new \DateTime($params["end"]));
            $trainingModule->setDuration($params["duration"]);
            $trainingModule->setSeats($params["seats"]);
        
            $entityManager->persist($trainingModule);
            $entityManager->flush();
            //echo $trainingModule->getId();exit;    
            return new JsonResponse(Array("data"=> "Training details have been added.","id"=>$trainingModule->getId()));
            
        
        
        }    

                }

      /**
     * @Route("/bookTraining/{id}/{userId}", name="bookTraining")
     */
    public function bookTraining($id, $userId, Request $request)
    {
       if ($request->isMethod('POST'))
        {
            
            $entityManager = $this->getDoctrine()->getManager();
            
            
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user =  $repository->find($userId);
            $repository = $this->getDoctrine()->getRepository(Training::class);
            $training =  $repository->find($id);
            $repository = $this->getDoctrine()->getRepository(Reservation::class);
            $reservation = $repository->findOneBy(["userId" => $user, "trainingId" => $training]);
            if($reservation)
            {
                return new JsonResponse(Array("data"=> "This training has already been reserved previously."));
            }
            if((date('Y-m-d') < $training->getStart()->format('Y-m-d'))||
               (date('Y-m-d') >= $training->getStart()->format('Y-m-d') && 
               date('Y-m-d') < $training->getEnd()->format('Y-m-d'))
              )
              {
                $reservation = new Reservation();
                $reservation->setUserId($user);
                $reservation->setTrainingId($training);
                $reservation->setActive(true);
                $training->setSeatsBooked((is_null($training->getSeatsBooked())?0:$training->getSeatsBooked()) + 1);
                $training->setSeats($training->getSeats() - 1);

                $entityManager->persist($reservation);
                $entityManager->persist($training);
                $entityManager->flush();
                
                return new JsonResponse(Array("data"=> "Training has been reserved."));
              } else {
                return new JsonResponse(Array("data"=> "Training is not active."));
              }
       
        }    

  }      
  
  
      /**
     * @Route("/bookedTraining/{userId}", name="bookedTraining")
     */
    public function viewBookedTraining($userId, Request $request)
    {
        if ($request->isXMLHttpRequest()) { 
          //  $requestBody = $request->getContent();
           // print_r($requestBody);
          
        }
        if ($request->isMethod('POST'))
        {
            
            $repository = $this->getDoctrine()->getRepository(User::class);
            $userId =  $repository->find($userId);
            $repository = $this->getDoctrine()->getRepository(Training::class);
            $trainings =  $repository->findBookedTrainings($userId);
            if($trainings)
              {
                foreach ($trainings as $training){

                    $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                    $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                }
                return new JsonResponse($output);
              } else {
                return new JsonResponse(Array("data"=> "No booked trainings are present."));
              }
       
        }    

  }        
  /**
     * @Route("/upcomingReservation/{userId}", name="upcomingReservation")
     */
    public function upcomingReservation($userId, Request $request)
    {
        if ($request->isMethod('POST'))
        {
            
            $repository = $this->getDoctrine()->getRepository(User::class);
            $userId =  $repository->find($userId);
            $repository = $this->getDoctrine()->getRepository(Training::class);
            $trainings =  $repository->findUpcomingReservations($userId);
            if($trainings)
              {
                foreach ($trainings as $training){

                    $output[]=array($training->getId(), $training->getUserId()->getId(), $training->getTopic(), $training->getDescription(),$training->getStart()->format('Y-m-d'),
                                    $training->getEnd()->format('Y-m-d'), $training->getDuration(), $training->getSeats(), $training->getSeatsBooked());
                }
                //print_r();exit;
                return new JsonResponse($output);
              } else {
                return new JsonResponse(Array("data"=> "No booked trainings are present."));
              }
       
        }    

  }        

  /**
     * @Route("/cancelReservation/{trainingId}/{userId}", name="cancelReservation")
     *
     */
    public function cancelReservation($trainingId, $userId, Request $request)
    { 
      
        if ($request->isMethod('DELETE'))
        {
            
            $entityManager = $this->getDoctrine()->getManager();
            
            
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user =  $repository->find($userId);
            $repository = $this->getDoctrine()->getRepository(Training::class);
            $training =  $repository->find($trainingId);
            if($training->getSeatsBooked()>0)
              {
                $repository = $this->getDoctrine()->getRepository(Reservation::class);  
                $reservation = $repository->findOneBy(["trainingId" =>$training,"userId" => $user]);
                //$reservation->setActive(false);
                $training->setSeatsBooked($training->getSeatsBooked() - 1);
                $training->setSeats($training->getSeats() + 1);
                $entityManager->remove($reservation);
                $entityManager->persist($training);
                $entityManager->flush();
                
                return new JsonResponse(Array("data"=> "Training has been cancelled."));
              } else {
                return new JsonResponse(Array("data"=> "Training is not booked."));
              }
        }    
     }

  
}
