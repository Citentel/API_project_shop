<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Users;
use App\Entity\Wishlist;
use App\Model\AbstractWishlist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WishlistController extends AbstractWishlist
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/add", methods={"POST"})
     */
    public function addList(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        /** @var Users $user */
        $user = $data['access_user'];

        $isUserHaveWishlist = $this->searchWishlistService->findOneByUser($user);

        if ($isUserHaveWishlist['code'] !== 404) {
            return $this->generateResponseService->generateJsonResponse($isUserHaveWishlist['code'], $isUserHaveWishlist['message'])['data'];
        }

        $wishlist = (new Wishlist())
            ->setName($data['name'])
            ->setUser($user);

        $this->entityManager->persist($wishlist);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added wishlist')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/remove", methods={"DELETE"})
     */
    public function removeList(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['wishlist_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isWishlistExist = $this->searchWishlistService->findOneById($data['wishlist_id']);

        if ($isWishlistExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isWishlistExist['code'], $isWishlistExist['message'])['data'];
        }

        $isUserHaveAccess = $this->accessUserToWishlist($isWishlistExist['data']['wishlist'], $data['access_user']);

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Wishlist $wishlist */
        $wishlist = $isUserHaveAccess['data']['wishlist'];

        /** @var Products[] $products */
        $products = $wishlist->getProducts()->getValues();

        if (!empty($products)) {
            foreach ($products as $product) {
                $wishlist->removeProduct($product);
            }
        }

        $this->entityManager->remove($wishlist);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'removed wishlist')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/clear", methods={"PATCH"})
     */
    public function clearList(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['wishlist_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isWishlistExist = $this->searchWishlistService->findOneById($data['wishlist_id']);

        if ($isWishlistExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isWishlistExist['code'], $isWishlistExist['message'])['data'];
        }

        $isUserHaveAccess = $this->accessUserToWishlist($isWishlistExist['data']['wishlist'], $data['access_user']);

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Wishlist $wishlist */
        $wishlist = $isUserHaveAccess['data']['wishlist'];

        /** @var Products[] $products */
        $products = $wishlist->getProducts()->getValues();

        if (!empty($products)) {
            foreach ($products as $product) {
                $wishlist->removeProduct($product);
            }
        }

        $this->entityManager->persist($wishlist);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'cleared wishlist')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/addProduct", methods={"PATCH"})
     */
    public function addProduct(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['wishlist_id', 'product_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isWishlistExist = $this->searchWishlistService->findOneById($data['wishlist_id']);

        if ($isWishlistExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isWishlistExist['code'], $isWishlistExist['message'])['data'];
        }

        $isUserHaveAccess = $this->accessUserToWishlist($isWishlistExist['data']['wishlist'], $data['access_user']);

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Wishlist $wishlist */
        $wishlist = $isUserHaveAccess['data']['wishlist'];

        $isProductExist = $this->searchProductsService->findOneById($data['product_id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateArrayResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $wishlist->addProduct($product);

        $this->entityManager->persist($wishlist);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added product to wishlist')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/removeProduct", methods={"DELETE"})
     */
    public function removeProduct(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['wishlist_id', 'product_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isWishlistExist = $this->searchWishlistService->findOneById($data['wishlist_id']);

        if ($isWishlistExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isWishlistExist['code'], $isWishlistExist['message'])['data'];
        }

        $isUserHaveAccess = $this->accessUserToWishlist($isWishlistExist['data']['wishlist'], $data['access_user']);

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Wishlist $wishlist */
        $wishlist = $isUserHaveAccess['data']['wishlist'];

        $isProductExist = $this->searchProductsService->findOneById($data['product_id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateArrayResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $wishlist->removeProduct($product);

        $this->entityManager->persist($wishlist);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'added product to wishlist')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/wishlist/get", methods={"GET"})
     */
    public function getList(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_USER');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['wishlist_id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isWishlistExist = $this->searchWishlistService->findOneById($data['wishlist_id']);

        if ($isWishlistExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isWishlistExist['code'], $isWishlistExist['message'])['data'];
        }

        $isUserHaveAccess = $this->accessUserToWishlist($isWishlistExist['data']['wishlist'], $data['access_user']);

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        /** @var Wishlist $wishlist */
        $wishlist = $isUserHaveAccess['data']['wishlist'];

        $wishlistResponse = [
            'id' => $wishlist->getId(),
            'name' => $wishlist->getName(),
            'user' => $wishlist->getUser()->getId(),
            'products' => [],
        ];

        /** @var Products[] $products */
        $products = $wishlist->getProducts()->getValues();

        if (!empty($products)) {
            foreach ($products as $product) {
                $wishlistResponse['products'][] = $this->searchProductsService->generateResponseProduct($product);
            }
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return wishlist', $wishlistResponse)['data'];
    }

    private function accessUserToWishlist(Wishlist $wishlist, Users $user): array
    {
        if ($wishlist->getUser()->getId() !== $user->getId() ) {
            return $this->generateResponseService->generateJsonResponse(409, 'user does not have access for this wishlist');
        }

        return $this->generateResponseService->generateArrayResponse(200, 'looks good', ['wishlist' => $wishlist]);
    }
}