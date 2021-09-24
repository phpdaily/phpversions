<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LastUpdateRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class DefaultController
{
    public function __construct(
        private LastUpdateRepository $repository,
    ) {
    }

    public function index(): Response
    {
        try {
            $lastUpdate = $this->repository->get()->format('Y-m-d H:i:s');
        } catch (RuntimeException $e) {
            $lastUpdate = $e->getMessage();
        }

        return new Response(<<<HTML
<html>
    <head>
        <title>phpversions</title>
    </head>
    <body>
        <p>An API that expose all PHP versions:</p>
        <ul>
            <li><a href="/all.json">All main PHP versions</a></li>
            <li><a href="/releases.json">All PHP releases versions</a></li>
            <li><a href="/current.json">Maintened versions</a></li>
            <li><a href="/eol.json">Unmaintened versions</a></li>
        </ul>
        <p>Last update: $lastUpdate</p>
    </body>
</html>
HTML
);
    }
}
