<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Image;
use App\Repository\ProductRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{

    #[Route('/product', name: 'product_list')]
    public function listProduct(ProductRepository $productRepository): Response
    {
        // récupère tous les produits
        $products = $productRepository->findAll();
        return $this->render('product/index.html.twig', ['products' => $products]);
    }


    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request, ProductRepository $productRepository, ImageRepository $imageRepository): Response
    {
        $product = new product();
        $image = new Image();

        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('file');

            if ($uploadedFile) {

                // gestion du nom du fichier
                $uploadsDirectory = 'uploads/images/products/';
                $filename = $request->request->get("name"). '.' .$uploadedFile->guessExtension();
                $filenameClean = str_replace(" ", "_", $filename);
                
                $image->setName($filenameClean);
                $image->setFilePath($uploadsDirectory . $filenameClean);
                $uploadedFile->move($uploadsDirectory, $filenameClean);
                
                $product->setName($request->request->get("name"));
                $product->setDescription($request->request->get("description"));
                $product->setPrice($request->request->get("price"));
                $product->setImagePath($uploadsDirectory . $filenameClean);
                
                $imageRepository->save($image, true);
                $productRepository->save($product, true);
            }
            // flash message et redirection sur la liste des produits
            $this->addFlash('success', 'Le produit a bien été créé.');
            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/create.html.twig', ['product' => $product]);
    }

    #[Route('/product/update/{product}', name: 'product_update')]
    public function updateProduct(Product $product, Request $request, ProductRepository $productRepository, ImageRepository $imageRepository): Response
    {   

        if ($request->isMethod('POST')) {

            $uploadedFile = $request->files->get('file');

            // Cas où une nouvelle image est uploadée
            if ($uploadedFile) {

                $image = new Image();

                $uploadsDirectory = 'uploads/images/products/';
                $filename = $request->request->get("name") . '.' . $uploadedFile->guessExtension();
                $filenameClean = str_replace(" ", "_", $filename);

                $image->setName($filenameClean);
                $image->setFilePath($uploadsDirectory . $filenameClean);
                $uploadedFile->move($uploadsDirectory, $filenameClean);
                $product->setImagePath($uploadsDirectory . $filenameClean);
                $imageRepository->save($image, true);
            }

            
            $product->setName($request->request->get("name"));
            $product->setDescription($request->request->get("description"));
            $product->setPrice($request->request->get("price"));

            $productRepository->save($product, true);

            // flash message et redirection sur la liste des produits
            $this->addFlash('success', 'Le produit a bien été modifié.');
            return $this->redirectToRoute('product_list');
        }


        return $this->render('product/create.html.twig', ['product' => $product]);
    }


    #[Route('/product/delete/{product}', name: 'product_delete')]
    public function deleteProduct(Product $product, ProductRepository $productRepository): Response
    {

        $productRepository->remove($product, true);

        // flash message et redirection sur la liste des produits
        $this->addFlash('success', 'Le produit a bien été supprimé.');
        return $this->redirectToRoute('product_list');
    
    }
}