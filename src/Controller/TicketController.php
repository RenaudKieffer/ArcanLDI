<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\SurveyTicket;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\GameRepository;
use App\Repository\SurveyRepository;
use App\Repository\TicketRepository;
use App\Service\uploadGamePhoto;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN')]
#[Route('/ticket')]
class TicketController extends AbstractController
{
    //crud ticket
    #[Route('/', name: 'ticket_index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository, GameRepository $gameRepository): Response
    {

        return $this->render('ticket/index.html.twig', [
            'games' => $gameRepository->findAll(),
            'tickets' => $ticketRepository->findAll(),
        ]);
    }

    //selection des tickets par jeu
    #[Route('/jeu/{id}', name: 'ticket_index_game', methods: ['GET'])]
    public function indexGame(TicketRepository $ticketRepository, Game $game, GameRepository $gameRepository): Response
    {
        $tickets = $ticketRepository->findByGame($game);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
            'games' => $gameRepository->findAll(),
        ]);
    }

    //créer un nouveau ticket

    #[Route('/nouveau', name: 'ticket_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, uploadGamePhoto $uploadGamePhoto): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //validation des CGV pour l'achat du ticket
            $ticket->setCgv($uploadGamePhoto->uploadCGVTicket($form->get('cgv')->getData(), $ticket));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket ajouté');
            return $this->redirectToRoute('ticket_show', [
                'id' => $ticket->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'ticket_show', methods: ['GET', 'POST'])]
    public function show(Ticket $ticket, Request $request, SurveyRepository $surveyRepository,EntityManagerInterface $entityManager): Response
    {

//        FORMULAIRE DES QUESTIONNAIRES SPECIFIQUE QUI NE SON PAS ENCORE ASSOCIE
        $formNotGeneral = $this->createFormBuilder()
            ->add('survey', EntityType::class, [
                'class' => 'App\Entity\Survey',
                'choices' => $surveyRepository->findByNotOnTicket(),
                'choice_label' => 'name',
            ])->getForm();
        $surveyTicket = new SurveyTicket();
        $surveyTicket->setTicket($ticket);

        $formNotGeneral->handleRequest($request);

        if ($formNotGeneral->isSubmitted() && $formNotGeneral->isValid()) {

            $surveyTicket->setSurvey($formNotGeneral->get('survey')->getData());

            $this->getDoctrine()->getManager()->persist($surveyTicket);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }
//        FORMULAIRE DES QUESTIONNAIRES GENERAUX QUI NE SON PAS ENCORE ASSOCIE A CE TICKET

        $listeSurvey = $surveyRepository->findByGeneral('1');
        $surveyAssocie = $surveyRepository->findBySurveyByTicket($ticket);

        foreach ($listeSurvey as $survey){

            if (!in_array($survey, $surveyAssocie)){
                $surveyAAfficher[] = $survey;
            }
        }
        if (!isset($surveyAAfficher)){
            $surveyAAfficher = [];
        }

        $formGeneral = $this->createFormBuilder()
            ->add('survey', EntityType::class, [
                'class' => 'App\Entity\Survey',
                'choices' => $surveyAAfficher,
                'choice_label' => 'name',
            ])->getForm();

        // Ajout des questionnaires "Généraux"
        $formGeneral->handleRequest($request);
        if ($formGeneral->isSubmitted() && $formGeneral->isValid()) {

            $surveyTicket->setSurvey($formGeneral->get('survey')->getData());

            $entityManager->persist($surveyTicket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }


        return $this->renderForm('ticket/show.html.twig', [
            'ticket' => $ticket,
            'formgeneral' => $formGeneral,
            'formnotgeneral' => $formNotGeneral,
        ]);


    }

    //modification d'un ticket
    #[Route('/{id}/modifier', name: 'ticket_edit', methods: ['GET', 'POST'])]
    public function edit(uploadGamePhoto $uploadGamePhoto, Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('cgv')->getData()) {
                $ticket->setCgv($uploadGamePhoto->uploadCGVTicket($form->get('cgv')->getData(), $ticket));
            }
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    //supression d'un ticket
    #[Route('/{id}/supprimer', name: 'ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket,EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
