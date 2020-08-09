<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

final class DefaultController
{
    public function index(): Response
    {
        return new Response(<<<HTML
<html>
    <body>
        An API that expose all PHP versions:
        <ul>
            <li><a href="/all.json">All main PHP versions</a></li>
            <li><a href="/releases.json">All PHP releases versions</a></li>
            <li><a href="/current.json">Maintened versions</a></li>
            <li><a href="/eol.json">Unmainted versions</a></li>
        </ul>
    </body>
</html>
HTML
);
    }
}
