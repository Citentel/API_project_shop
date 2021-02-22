<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMainType extends AbstractType
{
    abstract public function addSubTypeToMainType(Request $request): JsonResponse;

    abstract public function removeSubTypeFromMainType(Request $request): JsonResponse;

    abstract public function getSubTypesFromMainType(Request $request): JsonResponse;
}