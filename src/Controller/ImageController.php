<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Products;
use App\Model\AbstractImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractImage
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/image/add", methods={"POST"})
     */
    public function addImage(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['name', 'image'])
            ->setFieldsOptional(['display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isImageExist = $this->searchImageService->findOneByName($data['name']);

        if ($isImageExist['code'] === 200) {
            return $this->generateResponseService->generateJsonResponse(409, $isImageExist['message'])['data'];
        }

        $file = $this->imagesPath . $data['name'] . '.txt';

        $isFileAdded = file_put_contents($file, $data['image']);

        if (!$isFileAdded) {
            return $this->generateResponseService->generateJsonResponse(500, 'image does not add')['data'];             }

        $image = (new Images())
            ->setName($data['name'])
            ->setDisplay($data['display'] ?? true);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'image added')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/image/update", methods={"PATCH"})
     */
    public function updateImage(Request $request): JsonResponse
    {
        $isUserHaveAccess = $this->checkAccess($request, 'ROLE_ADMIN');

        if ($isUserHaveAccess['code'] !== 200) {
            return $isUserHaveAccess['data'];
        }

        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->setFieldsOptional(['name', 'display'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isImageExist = $this->searchImageService->findOneById($data['id']);

        if ($isImageExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isImageExist['code'], $isImageExist['message'])['data'];
        }

        /** @var Images $image */
        $image = $isImageExist['data']['image'];

        if (isset($data['name'])) {
            unlink($this->imagesPath . $image->getName() . '.txt');

            $file = $this->imagesPath . $data['name'] . '.txt';

            $isFileAdded = file_put_contents($file, $data['image']);

            if (!$isFileAdded) {
                return $this->generateResponseService->generateJsonResponse(500, 'image does not add')['data'];           }

            $image->setName($data['name']);
        }

        if (isset($data['display'])) {
            $image->setDisplay($data['display']);
        }

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $this->generateResponseService->generateJsonResponse(200, 'image update')['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/image/getOne", methods={"GET"})
     */
    public function getImage(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isImageExist = $this->searchImageService->findOneById($data['id']);

        if ($isImageExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isImageExist['code'], $isImageExist['message'])['data'];
        }

        /** @var Images $image */
        $image = $isImageExist['data']['image'];

        $fileImage = file_get_contents($this->imagesPath . $image->getName() . '.txt');

        $imageResponse = [
            'id' => $image->getId(),
            'name' => $image->getName(),
            'display' => $image->getDisplay(),
            'image' => $fileImage ? $fileImage : 'undefined',
        ];

        return $this->generateResponseService->generateJsonResponse(200, 'return image', $imageResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/image/getAll", methods={"GET"})
     */
    public function getImages(Request $request): JsonResponse
    {
        $images = $this->entityManager->getRepository(Images::class)->findAll();

        if (empty($images)) {
            return $this->generateResponseService->generateJsonResponse(404, 'database do not have any images')['data'];
        }

        $imagesResponse = [];

        /** @var Images $image */
        foreach ($images as $image) {
            $fileImage = file_get_contents($this->imagesPath . $image->getName() . '.txt');

            $imagesResponse[] = [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'display' => $image->getDisplay(),
                'image' => $fileImage ? $fileImage : 'undefined',
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return images', $imagesResponse)['data'];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("api/image/getByProduct", methods={"GET"})
     */
    public function getImageByProduct(Request $request): JsonResponse
    {
        $checkRequest = $this->checkRequestService
            ->setRequest($request)
            ->setFieldsRequired(['id'])
            ->checker();

        if ($checkRequest['code'] !== 200) {
            return $checkRequest['data'];
        }

        $data = $checkRequest['data'];

        $isProductExist = $this->searchProductsService->findOneById($data['id']);

        if ($isProductExist['code'] !== 200) {
            return $this->generateResponseService->generateJsonResponse($isProductExist['code'], $isProductExist['message'])['data'];
        }

        /** @var Products $product */
        $product = $isProductExist['data']['product'];

        $images = $product->getImages()->getValues();

        if (empty($images)) {
            return $this->generateResponseService->generateJsonResponse(404, 'product do not have any images')['data'];
        }

        $imagesResponse = [];

        /** @var Images $image */
        foreach ($images as $image) {
            $fileImage = file_get_contents($this->imagesPath . $image->getName() . '.txt');

            $imagesResponse[] = [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'display' => $image->getDisplay(),
                'image' => $fileImage ? $fileImage : 'undefined',
            ];
        }

        return $this->generateResponseService->generateJsonResponse(200, 'return images', $imagesResponse)['data'];
    }
}