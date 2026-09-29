<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repository\ProductRepository;
use Framework\Controller;
use Framework\Exceptions\PageNotFoundException;
use App\Models\Product;
use Framework\Response;

class Products extends Controller
{

    public function __construct(
        private ProductRepository $repository
    ) {}

    public function index(): Response
    {
        return $this->view("Products/index.mvc.php", [
            "products" => $this->repository->findAll(),
            "total" => $this->repository->getTotal(),
        ]);
    }

    public function show(string $id): Response
    {
        $product = $this->getProduct($id);

        return $this->view("Products/show.mvc.php", [
            "id" => $id,
            "product" => $product,
        ]);
    }

    public function new(): Response
    {
        $product = new Product();
        return $this->view("Products/new.mvc.php", [
            "product" => $product
        ]);
    }

    public function create(): Response
    {
        $product = new Product();
        $product->setName($this->request->post['name'])
                ->setDescription(empty($this->request->post['description']) ? null : $this->request->post['description']);

        if ($this->repository->insert($product)) {
            return $this->redirect("/products/{$this->repository->getInsertedId()}/show");
        } else {
            return $this->view("Products/new.mvc.php",  [
                "errors" => $product->getErrors()
            ]);
        }
    }

    public function edit(string $id): Response
    {
        $product = $this->getProduct($id);

        return $this->view("Products/edit.mvc.php", [
            "product" => $product,
        ]);
    }

    public function update(string $id): Response
    {
        $product = $this->getProduct($id);
        $product->setName($this->request->post['name'])
                ->setDescription(empty($this->request->post['description']) ? null : $this->request->post['description']);
       
        if ($this->repository->update($product)) {
            return $this->redirect("/products/$id/show");
        } else {
            return $this->view("Products/edit.mvc.php", [
                "product" => $product,
                "errors" => $product->getErrors(),
            ]);
        }
    }

    public function delete(string $id): Response
    {
        $product = $this->getProduct($id);

        if ($this->request->server["REQUEST_METHOD"] === "POST"){
            $this->repository->delete($product);
            return $this->redirect("/products/index");
        }

        return $this->view("Products/delete.mvc.php", [
            "product" => $product
        ]);
    }

    public function destroy(string $id): Response
    {
        $product = $this->getProduct($id);

        $this->repository->delete($product);
        return $this->redirect("/products/index");
    }

    public function responseCodeExample(): Response
    {
        $this->response->setStatusCode(451);
        $this->response->setBody("Unavailable for legal reasons");
        return $this->response;
    }

    public function getProduct(string $id): Product
    {
        $product = $this->repository->find($id);

         if (! $product || ! $product instanceof Product) {
            throw new PageNotFoundException("Product not found");
        }

        return $product;
    }

}
