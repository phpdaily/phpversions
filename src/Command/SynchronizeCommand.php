<?php

declare(strict_types=1);

namespace App\Command;

use App\{
    PhpVersionFetcher,
    Repository\PhpReleaseRepository,
    Repository\PhpVersionRepository,
};
use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
};

final class SynchronizeCommand extends Command
{
    public function __construct(
        private PhpReleaseRepository $releaseRepository,
        private PhpVersionRepository $versionRepository,
        private PhpVersionFetcher $fetcher,
    ) {
        parent::__construct('synchronize');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Process current versions</info>');
        $currents = $this->fetcher->currents();
        $this->versionRepository->save(...$currents);

        $output->writeln('<info>Process unmaintened versions</info>');
        $eol = $this->fetcher->eol();
        $this->versionRepository->save(...$eol);

        $output->writeln('<info>Process releases versions</info>');
        $releases = $this->fetcher->releases();
        $this->releaseRepository->save(...$releases);

        return 0;
    }
}
