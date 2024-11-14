<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use ApiResponse;

    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return $this->error('File not found', Response::HTTP_NOT_FOUND);
        } elseif ($e instanceof AuthorizationException) {
            return $this->error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }else if ($e instanceof ValidationException) {
            $errorMessages = array_map(fn($messages) => implode(' ', $messages), $e->errors());
            $formattedErrorMessage = implode(' ', $errorMessages);
            return $this->error($formattedErrorMessage, Response::HTTP_UNPROCESSABLE_ENTITY);;
        }

        return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
