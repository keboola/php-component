<?php

declare(strict_types=1);

namespace Keboola\Component\Tests\Logger;

class StreamTester extends \php_user_filter
{
    /** @var string */
    private static $content = '';

    /**
     * @param resource $stream
     */
    public static function attach($stream): void
    {
        @stream_filter_register('streamTester', self::class);
        stream_filter_append($stream, 'streamTester');
    }

    public static function getContent(): string
    {
        return self::$content;
    }

    /**
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            /** @var \stdClass $bucket */
            self::$content .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
