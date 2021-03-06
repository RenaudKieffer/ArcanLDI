<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Video;
use App\Form\AlbumVideoFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/video', name: 'video_')]
class VideoController extends AbstractController
{
    #[Route('/{slug}', name: 'see_video')]
    public function index(Game $game): Response
    {

        return $this->render('video/seeAlbum.html.twig',
        [
            'game'=>$game,
        ]
        );
    }
    #[Route('/ajouter-video/{id}', name: 'game_add_album_video', methods: ['POST', 'GET'])]
    public function addAlbumVideo(EntityManagerInterface $entityManager,Request $request, Game $game): Response
    {
        $form = $this->createForm(AlbumVideoFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            $url = $form->get('name')->getData();
            if (!filter_var($url, FILTER_VALIDATE_URL)){
                $form->addError(new FormError('Url invalide'));
            }
            if ($form->isValid()){
                $url = explode("=", $url)[1];
                $url = explode("&", $url)[0];
                $video = new Video();
                $video->setGame($game)
                    ->setName($url);

                $entityManager->persist($video);
                $entityManager->flush();
                $this->addFlash('success', 'Vidéo ajoutée avec succès.');
                return $this->redirectToRoute('video_game_add_album_video', ['id'=>$game->getId()]);
            }


        }
        return $this->renderForm('game/add_album_video.html.twig', [
            'form' => $form,
            'game' => $game,
        ]);
    }

    #[Route('/picture/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Video $video,
                           EntityManagerInterface $entityManager
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {
            $entityManager->remove($video);
            $entityManager->flush();
        }

        return $this->redirectToRoute('video_see_video', [
            'slug'=>$video->getGame()->getSlug()
        ], Response::HTTP_SEE_OTHER);
    }
}
