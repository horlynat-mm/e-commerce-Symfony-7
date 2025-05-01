<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/category', name: 'category_')]
final class CategoryController extends AbstractController
{
    #[Route('/', name: 'collection', methods: ['GET'])]
    public function collection(CategorieRepository $categorieRepository): Response
    {
        return $this->render('category/collection.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function addCategory(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setSlug($slugger->slug($categorie->getName())->lower());
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès !');
            return $this->redirectToRoute('category_collection', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/read', name: 'read', methods: ['GET'])]
    public function readCategory(Categorie $categorie): Response
    {
        return $this->render('category/read.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{slug}/update', name: 'update', methods: ['GET', 'POST'])]
    public function updateCategory(string $slug, Request $request, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = $categorieRepository->findOneBy(['slug' => $slug]);
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $form = $this->createForm(CategorieType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug($slugger->slug($category->getName())->lower());
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été mise à jour avec succès !');
            return $this->redirectToRoute('category_collection', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/update.html.twig', [
            'form' => $form->createView(),
            'categorie' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Action non autorisée.');
        }

        return $this->redirectToRoute('category_collection');
    }
}
