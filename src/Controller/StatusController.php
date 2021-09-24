<?php

declare(strict_types=1);

namespace App\Controller;

use App\PDOFactory;
use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\Response;

class StatusController
{
    public function __construct(
        private PDOFactory $factory,
    ) {
    }

    public function __invoke(): Response
    {
        try {
            $db = $this->factory->create();
        } catch (PDOException) {
            return new Response('FAILED: database connection error.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (false === $db->exec('SELECT 1')) {
            return new Response('FAILED: database access error.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
