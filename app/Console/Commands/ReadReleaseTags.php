<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class ReadReleaseTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update release tag config values';

    public const PATH = '.env';
    public const RELEASE_DATE_TAG = 'RELEASE_DATE';
    public const RELEASE_COMMIT_TAG = 'RELEASE_COMMIT';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!file_exists(self::PATH)) {
            $this->error('Could not find .env file');
            return Command::FAILURE;
        }

        $result = $this->writeReleaseDate();
        if ($result == Command::FAILURE) {
            return Command::FAILURE;
        }

        $result = $this->writeCommitHash();
        if ($result == Command::FAILURE) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Write the release date to the .env file.
     */
    private function writeReleaseDate(): int
    {
        // Read the contents of the .env file
        $environment = file_get_contents(self::PATH);
        if (!$environment) {
            $this->error('Could not read environment file.');
            return Command::FAILURE;
        }

        // Ensure the variable key is present in the .env file
        if (!strpos($environment, self::RELEASE_DATE_TAG)) {
            $this->error("The release date key is not present in the environment file.");

            return Command::FAILURE;
        }

        // Write the new asset version value to the .env file
        $now = now()->toAtomString();
        file_put_contents(self::PATH, preg_replace(
            $this->keyReplacementPattern(self::RELEASE_DATE_TAG, $_ENV[self::RELEASE_DATE_TAG]),
            self::RELEASE_DATE_TAG . '=' . $now,
            $environment,
        ));

        $this->info("Updated release date: $now");


        return Command::SUCCESS;
    }

    /**
     * Write the current commit hash to the .env file.
     */
    protected function writeCommitHash(): int
    {
        // Read the contents of the .env file
        $environment = file_get_contents(self::PATH);
        if (!$environment) {
            $this->error('Could not read environment file.');
            return Command::FAILURE;
        }

        // Ensure the variable key is present in the .env file
        if (!strpos($environment, self::RELEASE_COMMIT_TAG)) {
            $this->error("The release date key is not present in the environment file.");

            return Command::FAILURE;
        }

        // Write the new asset version value to the .env file
        $hash = exec('git log --pretty="%h" -n1 HEAD');
        if ($hash) {
            file_put_contents(self::PATH, preg_replace(
                $this->keyReplacementPattern(self::RELEASE_COMMIT_TAG, $_ENV[self::RELEASE_COMMIT_TAG]),
                self::RELEASE_COMMIT_TAG . '=' . trim($hash),
                $environment,
            ));
        }

        $this->info("Updated commit hash: $hash");

        return Command::SUCCESS;
    }

    /**
     * Generate a search needle for preg_replace.
     *
     * @param string $key
     * @param string $current
     */
    private function keyReplacementPattern($key, $current): string
    {
        $escaped = preg_quote('=' . $current);

        return "/^{$key}{$escaped}/m";
    }
}
