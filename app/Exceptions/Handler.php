<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use ApiResponse;

    public function render($request, Throwable $throwable)
    {
        if ($throwable instanceof ModelNotFoundException) {
            return $this->error('File not found', Response::HTTP_NOT_FOUND);
        }

        if ($throwable instanceof AuthorizationException) {
            return $this->error($throwable->getMessage(), Response::HTTP_FORBIDDEN);
        }

        if ($throwable instanceof ValidationException) {
            $errorMessages = array_map(fn ($messages): string => implode(' ', $messages), $throwable->errors());
            $formattedErrorMessage = implode(' ', $errorMessages);

            return $this->error($formattedErrorMessage, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->error($throwable->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);

    }
}
