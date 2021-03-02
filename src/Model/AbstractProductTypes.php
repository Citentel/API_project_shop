<?php


namespace App\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractProductTypes extends AbstractTypes
{
    abstract public function getProductByType(Request $request): JsonResponse;
}