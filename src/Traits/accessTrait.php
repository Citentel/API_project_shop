<?php

namespace App\Traits;

use App\Service\CheckPrivilegesService;
use Symfony\Component\HttpFoundation\Request;

trait accessTrait
{
    protected CheckPrivilegesService $checkPrivilegesService;

    final protected function checkAccess(Request $request, string $roleName): array
    {
        return $this->checkPrivilegesService->checkPrivileges($request, $roleName);
    }
}