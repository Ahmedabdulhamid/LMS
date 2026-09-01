<?php

namespace Tests\Unit;

use App\Services\HlsVideoTranscoder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HlsVideoTranscoderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('video.renditions', [
            1080 => [],
            720 => [],
            480 => [],
            360 => [],
        ]);
    }

    #[DataProvider('sourceHeights')]
    public function test_it_never_upscales_renditions(int $height, array $expected): void
    {
        $service = new HlsVideoTranscoder;

        self::assertSame($expected, $service->qualitiesFor($height));
    }

    public static function sourceHeights(): array
    {
        return [
            '1080p' => [1080, [1080, 720, 480, 360]],
            '720p' => [720, [720, 480, 360]],
            '480p' => [480, [480, 360]],
            '360p' => [360, [360]],
        ];
    }
}
