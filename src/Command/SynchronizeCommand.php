<?php

declare(strict_types=1);

namespace App\Command;

use App\{
    Clock,
    PhpVersionFetcher,
    Storage,
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
        private PhpVersionFetcher $fetcher,
        private Storage $storage,
        string $name
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $versions = [];

        $this->log($output, 'Fetch current versions');
        foreach ($this->fetcher->currents() as $version) {
            $versions[] = $version;
        }

        $this->log($output, 'Fetch unmaintened versions');
        foreach ($this->fetcher->eol() as $version) {
            $versions[] = $version;
        }

        $this->log($output, 'Fetch releases versions');
        $releases = $this->fetcher->releases();

        $this->log($output, 'Write data');
        $this->storage->write($versions, $releases);

        return 0;
    }

    private function log(OutputInterface $output, string $text): void
    {
        $currentTime = $this->clock->now()->format('Y-m-d H:i:s');
        $output->writeln("<info>[$currentTime] $text</info>");
    }
}
