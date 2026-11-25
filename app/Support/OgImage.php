<?php

namespace App\Support;

use GdImage;

/**
 * Renders the 1200×630 Open Graph card: the hero headline on the left and the
 * "11+" cell numeral on the right. Drawn at 2× and downsampled so shapes and
 * text come out antialiased.
 */
final class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    private const SCALE = 2;

    private const PAD = 64;

    private const CELL = 26;

    private const RAMP = ['#ddf3ea', '#b6e8d4', '#84d8b8', '#4cc49a', '#00a27a', '#00785b'];

    private GdImage $img;

    public function __construct(
        private readonly string $displayFont,
        private readonly string $textFont,
    ) {}

    /** @param array{0: string, 1: string} $lines */
    public function render(array $lines, string $caption): string
    {
        $s = self::SCALE;
        $this->img = imagecreatetruecolor(self::WIDTH * $s, self::HEIGHT * $s);
        imagesavealpha($this->img, false);
        imagefilledrectangle($this->img, 0, 0, self::WIDTH * $s, self::HEIGHT * $s, $this->color('#f7f7f5'));

        $gridW = Cells::COLS * self::CELL;
        $gridH = Cells::ROWS * self::CELL;
        $textW = self::WIDTH - 2 * self::PAD - $gridW - 24;

        // Headline: two lines, tight tracking, shrunk if a long translation overflows
        $size = 84;
        foreach ($lines as $line) {
            $w = $this->measure($line, $size, -4);
            if ($w > $textW) {
                $size = min($size, (int) floor($size * $textW / $w));
            }
        }
        $baseline = self::PAD + $size * 0.8;
        foreach ($lines as $line) {
            $this->text($line, self::PAD, $baseline, $size, -$size / 21, '#141518', $this->displayFont);
            $baseline += $size * 0.95;
        }

        // Caption, wrapped to 640px and pinned to the bottom
        $wrapped = $this->wrap($caption, 30, 640);
        $y = self::HEIGHT - self::PAD - 8 - (count($wrapped) - 1) * 38;
        foreach ($wrapped as $row) {
            $this->text($row, self::PAD, $y, 30, 0, '#5a5f66', $this->textFont);
            $y += 38;
        }

        // The cell field
        $on = Cells::numeral('11+');
        $gx = self::WIDTH - self::PAD - $gridW;
        $gy = (self::HEIGHT - $gridH) / 2;
        for ($slot = 0; $slot < Cells::COLS * Cells::ROWS; $slot++) {
            $x = $gx + ($slot % Cells::COLS) * self::CELL + 2;
            $row = intdiv($slot, Cells::COLS);
            $y = $gy + $row * self::CELL + 2;
            $d = self::CELL - 4;
            if (isset($on[$slot])) {
                $tone = self::RAMP[(int) floor($row / Cells::ROWS * count(self::RAMP))];
                $this->roundRect($x, $y, $d, 4, $tone);
            } else {
                $this->roundRect($x, $y, $d, 4, '#d4d6d2');
                $this->roundRect($x + 1, $y + 1, $d - 2, 3, '#f7f7f5');
            }
        }

        $out = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($out, $this->img, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, self::WIDTH * $s, self::HEIGHT * $s);

        ob_start();
        imagepng($out, null, 9);

        return (string) ob_get_clean();
    }

    /** GD sizes are points at 96 dpi; CSS sizes are pixels. */
    private function pt(float $px): float
    {
        return $px * self::SCALE * 0.75;
    }

    /** The glyph's advance width, side bearings included. */
    private function advance(string $ch, float $px, string $font): float
    {
        $width = fn (string $s) => ($b = imagettfbbox($this->pt($px), 0, $font, $s))[2] - $b[0];

        return ($width("H{$ch}H") - $width('HH')) / self::SCALE;
    }

    private function measure(string $text, float $px, float $tracking, ?string $font = null): float
    {
        $font ??= $this->displayFont;
        $w = 0;
        foreach (mb_str_split($text) as $ch) {
            $w += ($ch === ' ' ? $px * 0.25 : $this->advance($ch, $px, $font)) + $tracking;
        }

        return $w - $tracking;
    }

    private function text(string $text, float $x, float $baseline, float $px, float $tracking, string $hex, string $font): void
    {
        if ($tracking === 0.0) {
            imagettftext($this->img, $this->pt($px), 0, (int) ($x * self::SCALE), (int) ($baseline * self::SCALE), $this->color($hex), $font, $text);

            return;
        }
        foreach (mb_str_split($text) as $ch) {
            if ($ch !== ' ') {
                imagettftext($this->img, $this->pt($px), 0, (int) round($x * self::SCALE), (int) ($baseline * self::SCALE), $this->color($hex), $font, $ch);
            }
            $x += ($ch === ' ' ? $px * 0.25 : $this->advance($ch, $px, $font)) + $tracking;
        }
    }

    /** @return list<string> */
    private function wrap(string $text, float $px, float $max): array
    {
        $rows = [];
        $current = '';
        foreach (explode(' ', $text) as $word) {
            $try = $current === '' ? $word : "{$current} {$word}";
            $box = imagettfbbox($this->pt($px), 0, $this->textFont, $try);
            if ($current !== '' && ($box[2] - $box[0]) / self::SCALE > $max) {
                $rows[] = $current;
                $current = $word;
            } else {
                $current = $try;
            }
        }
        $rows[] = $current;

        return $rows;
    }

    private function roundRect(float $x, float $y, float $size, float $r, string $hex): void
    {
        $s = self::SCALE;
        [$x, $y, $size, $r] = [(int) round($x * $s), (int) round($y * $s), (int) round($size * $s), (int) round($r * $s)];
        $c = $this->color($hex);
        imagefilledrectangle($this->img, $x + $r, $y, $x + $size - $r - 1, $y + $size - 1, $c);
        imagefilledrectangle($this->img, $x, $y + $r, $x + $size - 1, $y + $size - $r - 1, $c);
        foreach ([[$x + $r, $y + $r], [$x + $size - $r - 1, $y + $r], [$x + $r, $y + $size - $r - 1], [$x + $size - $r - 1, $y + $size - $r - 1]] as [$cx, $cy]) {
            imagefilledellipse($this->img, $cx, $cy, $r * 2, $r * 2, $c);
        }
    }

    private function color(string $hex): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocate($this->img, $r, $g, $b);
    }
}
