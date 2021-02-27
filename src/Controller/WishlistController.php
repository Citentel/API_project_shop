<?php

namespace App\Controller;

use App\Model\AbstractWishlist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WishlistController extends AbstractWishlist
{
    public function addList(Request $request): JsonResponse
    {
        // TODO: Implement addList() method.
    }

    public function removeList(Request $request): JsonResponse
    {
        // TODO: Implement removeList() method.
    }

    public function clearList(Request $request): JsonResponse
    {
        // TODO: Implement clearList() method.
    }

    public function addProduct(Request $request): JsonResponse
    {
        // TODO: Implement addProduct() method.
    }

    public function removeProduct(Request $request): JsonResponse
    {
        // TODO: Implement removeProduct() method.
    }

    public function getList(Request $request): JsonResponse
    {
        // TODO: Implement getList() method.
    }
}