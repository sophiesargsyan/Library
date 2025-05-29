<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class BookController extends AbstractController
{
    #[Route('/books', name: 'book_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $books = $entityManager->getRepository(Book::class)->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/books/new', name: 'book_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Վերցնում ենք վերբեռնված նկարը՝ coverImage դաշտից
            $coverImageFile = $form->get('coverImage')->getData();

            if ($coverImageFile) {
                // Ստեղծում ենք անվտանգ ֆայլի անուն՝ օրինակ՝ my-book-cover.jpg
                $originalFilename = pathinfo($coverImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverImageFile->guessExtension();

                try {
                    $coverImageFile->move(
                        $this->getParameter('cover_images_directory'), // սա config-ից է գալիս
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Ֆայլի վերբեռնումը ձախողվեց։');
                    return $this->redirectToRoute('book_new');
                }

                // Գրքի վրա պահում ենք միայն ֆայլի անունը
                $book->setCoverImage($newFilename);
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}