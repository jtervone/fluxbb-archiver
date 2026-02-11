<?php

declare(strict_types=1);

namespace FluxbbArchiver\Console;

class CliOutput
{
    public function info(string $message): void
    {
        echo $message . "\n";
    }

    public function success(string $message): void
    {
        echo "\033[32m" . $message . "\033[0m\n";
    }

    public function error(string $message): void
    {
        fwrite(STDERR, "\033[31mError: " . $message . "\033[0m\n");
    }

    public function heading(string $message): void
    {
        echo "\n" . $message . "\n";
        echo str_repeat('=', strlen($message)) . "\n";
    }

    public function blank(): void
    {
        echo "\n";
    }
}
