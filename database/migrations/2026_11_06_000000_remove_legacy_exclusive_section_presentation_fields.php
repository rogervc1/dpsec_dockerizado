<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->select(['id', 'exclusive_page_content'])
            ->whereNotNull('exclusive_page_content')
            ->orderBy('id')
            ->chunkById(200, function ($events): void {
                foreach ($events as $event) {
                    $sections = is_array($event->exclusive_page_content)
                        ? $event->exclusive_page_content
                        : json_decode($event->exclusive_page_content, true);

                    if (!is_array($sections)) continue;

                    $changed = false;
                    foreach ($sections as &$section) {
                        if (!is_array($section)) continue;
                        $changed = $changed || array_key_exists('title', $section) || array_key_exists('content', $section);
                        unset($section['title'], $section['content']);
                    }
                    unset($section);

                    if ($changed) {
                        DB::table('events')->where('id', $event->id)->update([
                            'exclusive_page_content' => json_encode($sections),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Removed values were presentation-only and cannot be reconstructed.
    }
};
