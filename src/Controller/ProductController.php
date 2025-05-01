<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use App\Repository\CategorieRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product', name: 'product_')]
final class ProductController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(ProductsRepository $productsRepository): Response
    {
        return $this->render('product/list.html.twig', [
            'products' => $productsRepository->findAll(),
        ]);
    }

    #[Route('/{slug}/collection', name: 'collection', methods: ['GET'])]
    public function collection(string $slug, CategorieRepository $categorieRepository, ProductsRepository $productsRepository): Response
    {
        $category = $categorieRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $products = $productsRepository->findBy(['category' => $category]);

        return $this->render('product/collection.html.twig', [
            'products' => $products,
            'category' => $category,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($slugger->slug($product->getName())->lower());
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('product_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/read', name: 'read', methods: ['GET'])]
    public function readProduct(Products $product): Response
    {
        return $this->render('product/read.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{slug}/update', name: 'update', methods: ['GET', 'POST'])]
    public function updateProduct(Request $request, Products $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($slugger->slug($product->getName())->lower());
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès !');
            return $this->redirectToRoute('product_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/update.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function deleteProduct(Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Action non autorisée.');
        }

        return $this->redirectToRoute('product_list', [], Response::HTTP_SEE_OTHER);
    }
}
