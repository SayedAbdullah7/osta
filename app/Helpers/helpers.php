<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

if (!function_exists('log_content')) {
    function log_content($content, bool $save = true): void
    {
//        Log::channel('content')->debug($content);
//        if (is_array($content) || is_object($content)) {
//            $content = json_encode($content, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
//        }

        if ($save) {
            $data = [];

            if (Storage::exists('content.json')) {
                $data = json_decode(Storage::get('content.json'), true, 512, JSON_THROW_ON_ERROR);
            }

            $data[] = [
                'content' => $content,
                'time' => now(),
            ];

            Storage::put('content.json', json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}

if (!function_exists('clear_log')) {
    function clear_log(): void
    {
        Storage::put('content.json', json_encode([], JSON_PRETTY_PRINT));
        Log::channel('content')->info("Content log cleared.");
    }
}

if (!function_exists('show_log')) {
    function show_log(): array
    {
        if (!Storage::exists('content.json')) {
            return [];
        }

        return json_decode(Storage::get('content.json'), true);
    }
}

if (!function_exists('p')) {
    /**
     * Print content line by line.
     * If array/object, print as pretty JSON.
     *
     * @param mixed $content
     * @return void
     * @throws JsonException
     */
    function p($content): void
    {
//        echo 'ss';

        return;
        if (is_array($content) || is_object($content)) {
            echo json_encode($content, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
        } else {
            echo$content . PHP_EOL;
//            foreach (explode("\n", (string) $content) as $line) {
//                echo $line . PHP_EOL;
//            }
        }
    }
}

