<?php

declare(strict_types=1);

namespace App\Command;

use App\{
    Clock,
    PhpVersionFetcher,
    Repository\PDO\LastUpdateRepository,
    Repository\PDO\PdoPhpReleaseRepository,
    Repository\PDO\PdoPhpVersionRepository,
};
use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
};

final class SynchronizeCommand extends Command
{
    public function __construct(
        private Clock $clock,
        private LastUpdateRepository $lastUpdateRepository,
        private PdoPhpReleaseRepository $releaseRepository,
        private PdoPhpVersionRepository $versionRepository,
        private PhpVersionFetcher $fetcher,
    ) {
        parent::__construct('synchronize');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentTime = $this->clock->now()->format('Y-m-d H:i:s');
        $output->writeln("<info>[$currentTime] Process current versions</info>");
        $currents = $this->fetcher->currents();
        $this->versionRepository->save(...$currents);

        $currentTime = $this->clock->now()->format('Y-m-d H:i:s');
        $output->writeln("<info>[$currentTime] Process unmaintened versions</info>");
        $eol = $this->fetcher->eol();
        $this->versionRepository->save(...$eol);

        $currentTime = $this->clock->now()->format('Y-m-d H:i:s');
        $output->writeln("<info>[$currentTime] Process releases versions</info>");
        $releases = $this->fetcher->releases();
        $this->releaseRepository->save(...$releases);

        $this->lastUpdateRepository->save();

        return 0;
    }
}
