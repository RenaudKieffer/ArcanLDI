<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\RoleGroupe;
use App\Entity\User;
use App\Form\RoleAddFormType;
use App\Form\RoleGroupeType;
use App\Repository\OrderRepository;
use App\Repository\RoleGroupeRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/role')]
#[IsGranted('ROLE_MODERATOR')]
class RoleGroupeController extends AbstractController
{
    #[Route('/', name: 'role_groupe_index', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(RoleGroupeRepository $roleGroupeRepository): Response
    {
        return $this->render('role_groupe/index.html.twig', [
            'role_groupes' => $roleGroupeRepository->findAllButPublic(),
        ]);
    }

    #[Route('/new', name: 'role_groupe_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function new(Request $request): Response
    {
        $roleGroupe = new RoleGroupe();
        $form = $this->createForm(RoleGroupeType::class, $roleGroupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($roleGroupe);
            $entityManager->flush();

            return $this->redirectToRoute('role_groupe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role_groupe/new.html.twig', [
            'role_groupe' => $roleGroupe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/joueur-liste', name: 'show_role_player_list', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function showRolePlayerList(RoleGroupe $role,UserRepository $userRepository): Response
    {
       $player = $userRepository->findPlayerWithRole($role->getGame(),$role);
        return $this->render('role_groupe/player_list.html.twig', [
            'players' => $player,
            'role' => $role,
        ]);
    }


    #[Route('/{id}/edit', name: 'role_groupe_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function edit(Request $request, RoleGroupe $roleGroupe): Response
    {
        $form = $this->createForm(RoleGroupeType::class, $roleGroupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('role_groupe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role_groupe/edit.html.twig', [
            'role' => $roleGroupe,
            'form' => $form,
        ]);
    }
    //ajout d'un player a role de groupe
    #[Route('/{id}/ajouter', name: 'role_groupe_add', methods: ['GET','POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function add(Request $request, RoleGroupe $role, UserRepository $userRepository): Response
    {
        $form = $this->createForm(RoleAddFormType::class,[],[
            'choice'=> $userRepository->findPlayerWithoutRole($role->getGame(),$role),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->get('name')->getData() as $player){
            $player->addRoleGroupe($role);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('role_groupe_index',[
                'id' => $role->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role_groupe/show.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'role_groupe_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(Request $request, RoleGroupe $roleGroupe): Response
    {
        if ($this->isCsrfTokenValid('delete'.$roleGroupe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($roleGroupe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('role_groupe_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/delete/{pseudo}-{id}', name: 'role_delete', methods: ['POST','GET'])]
    #[ParamConverter('user', options: ['mapping' => ['pseudo' => 'pseudo']])]
    #[ParamConverter('roleGroupe', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_MODERATOR')]
    public function deleteRole(User $user,RoleGroupe $roleGroupe, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getRepository(Order::class);
        $id = $entityManager->findOneByPlayer($user)->getTicket()->getId();

        if ($this->isCsrfTokenValid('delete'.$roleGroupe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->removeRoleGroupe($roleGroupe);
            $entityManager->flush();
        } else{
            $this->addFlash('error','role non retirer');
        }

        return $this->redirectToRoute('show_role_player_list', [
            'id' => $roleGroupe->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
