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

    #[Route('/product/list', name: 'product_list')]
    public function listProduct(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/list.html.twig', ['products' => $products]);
    }


    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request, ProductRepository $productRepository, ImageRepository $imageRepository): Response
    {
        $product = new product();
        $image = new Image();

        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('file');

            if ($uploadedFile) {

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

        }

        return $this->render('product/index.html.twig', ['product' => $product]);
    }

    #[Route('/product/update/{product}', name: 'product_update')]
    public function updateProduct(Product $product, Request $request, ProductRepository $productRepository): Response
    {

        dump($product);
        if ($request->isMethod('POST')) {

            $product->setName($request->request->get("name"));
            $product->setDescription($request->request->get("description"));
            $product->setPrice($request->request->get("price"));

            $productRepository->save($product, true);
        }


        return $this->render('product/index.html.twig', ['product' => $product]);
    }


    #[Route('/product/delete/{product}', name: 'product_delete')]
    public function deleteProduct(Product $product, ProductRepository $productRepository): Response
    {

        $productRepository->remove($product, true);



        return $this->render('product/index.html.twig', ['product' => $product]);
    }
}