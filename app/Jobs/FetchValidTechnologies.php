<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class FetchValidTechnologies implements ShouldQueue
{
    use Queueable;

    private const string API_URL = 'https://api.stackexchange.com/2.3/tags?pagesize=100&order=desc&sort=popular&site=stackoverflow';

    private const string CACHE_FILE = 'valid_technologies.json';

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tags = [];

        for ($page = 1; $page < 6; $page++) {
            $api_url = $page > 1 ? substr_replace(self::API_URL, "page=$page&", 39, 0) : self::API_URL;

            // Fetch the list of valid technologies from the external API.
            $response = Http::timeout(1)->async(false)->get($api_url);
            if (! $response->successful()) {
                throw new RuntimeException('Failed to fetch tags from API.');
            }

            // Parse and validate the response.
            if (is_array($data = json_decode($response->body(), true)) === false) {
                throw new RuntimeException('Failed to parse tags API response: '.json_last_error_msg());
            }
            if (! isset($data['items']) || ! is_array($data['items'])) {
                throw new RuntimeException("API response is missing expected 'items' data.");
            }

            $tags = array_merge($tags, array_column($data['items'], 'name'));
        }

        // Cache the valid technologies.
        if (($tags = json_encode($tags)) === false) {
            throw new RuntimeException('Failed to encode technology tags to JSON.');
        }
        Storage::put(self::CACHE_FILE, $tags);

        // Log the successful update.
        Log::info('Valid technology list cache has been updated.');
    }
}
