<?php

namespace App\Controller;

use App\Entity\Book; // Այս ֆայլը պետք է import անել, որպեսզի կարողանանք օգտագործել Book entity-ն։
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    // name: 'book_index' — սա route-ի անունն է, որը օգտագործվում է, երբ օրինակ՝ ուզում ես Twig-ում կամ PHP-ում հղում անել այս էջին:
    #[Route('/books', name: 'book_index')]
    // Այս ֆունկցիան վերցնում է Book աղյուսակից բոլոր գրքերը և փոխանցում է Twig ֆայլին (index.html.twig), որպեսզի դրանք ցուցադրվեն էջում։
    public function index(EntityManagerInterface $entityManager): Response
    // Symfony-ում հաճախ ֆունկցիաներ կամ class-եր չեն ստեղծում իրենց ներսում այն, ինչ իրենց պետք է, այլ ստանում են դրանք որպես dependency։
    {
        //Սա վերցնում է Book entity-ի “գրադարանը” (repository), որը հնարավորություն է տալիս փնտրել տվյալներ։
        // findAll() մեթոդը վերադարձնում է բոլոր գրքերը, որոնք պահվում են տվյալ աղյուսակում։
        $books = $entityManager -> getRepository(Book::class) ->findAll();
        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }
}
