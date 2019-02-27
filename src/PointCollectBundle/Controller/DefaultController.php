<?php

namespace PointCollectBundle\Controller;

use PointCollectBundle\Entity\Markers;
use PointCollectBundle\Entity\TypePointCollect;
use PointCollectBundle\Form\MarkersType;
use PointCollectBundle\Form\SearchAddress;
use PointCollectBundle\Form\SearchType;
use PointCollectBundle\Form\TypePointCollectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {


       $lat =36.8374946 ;
       $lng= 10.1927389;
       $type = "Carrefour Market";


        $em = $this->getDoctrine()->getManager();
        $RAW_QUERY = 'SELECT m.address,m.lat,m.lng,p.name,m.name, p.name, (6378 * acos(cos(radians(' . $lat . ')) * cos(radians(m.lat)) * cos(radians(m.lng) - radians(' . $lng . ')) + sin(radians(' . $lat . ')) * sin(radians(m.lat)))) AS distance FROM markers m , type_point_collect p where p.id = m.type_point_collect_id having distance <= 20 ';

        $type = $this->getDoctrine()->getRepository(TypePointCollect::class)->findAll();

        // ACOS(SIN(RADIANS(B2))*SIN(RADIANS(B3))+COS(RADIANS(B2))*COS(RADIANS(B3))*COS(RADIANS(C2-C3)))*6371

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $result = $statement->fetchAll();


           /* $nom=$request->get('findAddress').'%';

            $posts = $this->getDoctrine()->
            getRepository(Markers::class)->findLike($nom);*/

        return $this->render('@PointCollect/Default/index.html.twig',array('locationPoint'=>$result,'type'=>$type));


        //var_dump($result).die();
    }


    public function itineraireMapsAction(Request $request)
    {

            $nom=$request->get('findAddress').'%';
            $posts = $this->getDoctrine()->
            getRepository(Markers::class)->findLike($nom);

        return $this->render('@PointCollect/Default/itineraireMaps.html.twig', array('locationPoint' => $posts));


    }

 /*   public function OrderprixdescAction(Request $request) {


        $Post = new Markers();
        $form = $this->createForm(SearchAddress::class, $Post);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $type = $this->getDoctrine()->getRepository(TypePointCollect::class)->findAll();
            $nom=$Post->getAddress().'%';

            $posts = $this->getDoctrine()->
            getRepository(Markers::class)->findLike($nom);
            return $this->render('@PointCollect/Default/index.html.twig', array('locationPoint' => $posts,'type'=>$type));
        }
        $formview = $form->createView();
        return $this->render('@PointCollect/Default/itineraireMaps.html.twig', array('form' => $formview));


    }

*/

    public function searchLikeAddressAction(Request $request)
    {

       /* $names = array();
        $term = trim(strip_tags($request->get('address')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Markers::class)->findLike($term);

        foreach ($entities as $entity)
        {
            $names[] = $entity->getAddress();
        }

        $response = new JsonResponse();
        $response->setData($names);
        return $response;
*/



    }




    public function addLocationAction(Request $request)
    {
        $v = new Markers();
        $form = $this->createForm(MarkersType::class,$v);
        $form->handleRequest($request);
        if ($form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($v);
            $em->flush();
            return $this->redirectToRoute("showLocationData");
        }
        return $this->render("@PointCollect/Default/addLocation.html.twig",array('form'=>$form->createView()));

    }

    public function showLocationDataAction()
    {
        $locations = $this->getDoctrine()->getRepository(Markers::class)->findAll();
        return $this->render("@PointCollect/Default/showLocationData.html.twig",array('locationPoint'=>$locations));
    }

    public function showDetailsLocationsAction($id)
    {
        $locations = $this->getDoctrine()->getRepository(Markers::class)->findBySlug($id);
       // var_dump($locations).die();
        return $this->render("@PointCollect/Default/showDetailsLocation.html.twig",array('locationPoint'=>$locations));
    }

    public function deleteLocationAction($id)
    {
        $em= $this->getDoctrine()->getManager();
        $p = $this->getDoctrine()->getRepository(Markers::class)->findBySlug($id);
        $em->remove($p);
        $em->flush();
        return $this->redirectToRoute("showLocationData");
    }

    public function updateLocationAction(Request $request,$id)
    {
        $m=$this->getDoctrine()->getManager();
        $post=$m->getRepository("PointCollectBundle:Markers")->findBySlug($id);
        $form=$this->createForm(MarkersType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $m->persist($post);
            $m->flush();
            return $this->redirect($this->generateUrl('showLocationData'));
        }
        $formview = $form->createView();

        return $this->render('@PointCollect/Default/updateLocation.html.twig', array('form' => $formview));
    }

    public function addTypePointCollectAction($type)
    {

        $type = $this->getDoctrine()->getRepository(TypePointCollect::class)->findByType($type);
        return $this->render('@PointCollect/Default/addType.html.twig', array('locationPoint' => $type));

    }

    public function showTypePointCollectAction(Request $request)
    {
        $v = new TypePointCollect();
        $form = $this->createForm(TypePointCollectType::class,$v);
        $form->handleRequest($request);
        if ($form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($v);
            $em->flush();
            return $this->redirectToRoute("showTypePointCollect");
        }


        $locations = $this->getDoctrine()->getRepository(TypePointCollect::class)->findAll();
        // var_dump($locations).die();
        return $this->render("@PointCollect/Default/showType.html.twig",array('locationPoint'=>$locations,'form'=>$form->createView()));
    }


    public function deleteTypePointCollectAction($id)
    {
        $em= $this->getDoctrine()->getManager();
        $p = $this->getDoctrine()->getRepository(TypePointCollect::class)->find($id);
        $em->remove($p);
        $em->flush();
        return $this->redirectToRoute("showTypePointCollect");
    }

    public function updateTypePointCollectAction(Request $request,$id)
    {
        $m=$this->getDoctrine()->getManager();
        $post=$m->getRepository("PointCollectBundle:TypePointCollect")->find($id);
        $form=$this->createForm(TypePointCollectType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $m->persist($post);
            $m->flush();
            return $this->redirect($this->generateUrl('showTypePointCollect'));
        }
        $formview = $form->createView();

        return $this->render('@PointCollect/Default/updateType.html.twig', array('form' => $formview));
    }



    public function OrderprixdescAction(Request $request)
    {


        $evenements = $this->getDoctrine()
            ->getRepository('PointCollectBundle:Markers')
            ->findByType();

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new
        JsonEncoder()));
        $json = $serializer->serialize($evenements, 'json');
        return new Response($json);
    }


}
