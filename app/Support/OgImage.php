<?php

namespace App\Support;

use GdImage;

/**
 * The 1200×630 Open Graph card in the site's style: light canvas, the name
 * and the hero statement on the left, the portrait on a dark card on the
 * right. Drawn at 2× and downsampled so text and corners come out smooth.
 */
final class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    private const SCALE = 2;

    private const PAD = 64;

    private const BG = '#ecece9';

    private GdImage $img;

    public function __construct(
        private readonly string $boldFont,
        private readonly string $regularFont,
    ) {}

    public function render(string $name, string $statement, string $caption, ?string $photoPath = null): string
    {
        $s = self::SCALE;
        $this->img = imagecreatetruecolor(self::WIDTH * $s, self::HEIGHT * $s);
        imagealphablending($this->img, true);
        imagefilledrectangle($this->img, 0, 0, self::WIDTH * $s, self::HEIGHT * $s, $this->color(self::BG));

        // Portrait card
        $card = ['x' => 790, 'y' => self::PAD, 'w' => self::WIDTH - self::PAD - 790, 'h' => self::HEIGHT - 2 * self::PAD, 'r' => 36];
        $this->roundRect($card['x'], $card['y'], $card['w'], $card['h'], $card['r'], '#111113');
        if ($photoPath && is_file($photoPath) && ($photo = @imagecreatefromstring((string) file_get_contents($photoPath)))) {
            $pw = $card['w'];
            $ph = (int) round(imagesy($photo) * $pw / imagesx($photo));
            imagecopyresampled($this->img, $photo, $card['x'] * $s, ($card['y'] + $card['h'] - $ph) * $s, 0, 0, $pw * $s, $ph * $s, imagesx($photo), imagesy($photo));
            $this->clipCorners($card['x'], $card['y'], $card['w'], $card['h'], $card['r']);
        }

        $textW = $card['x'] - self::PAD - 48;

        // Name, then the statement as large as fits in four lines
        $this->text($name, self::PAD, self::PAD + 30, 34, -1.2, '#111113', $this->boldFont);
        $size = 70;
        do {
            $lines = $this->wrap($statement, $size, $textW, $this->boldFont, -$size * 0.04);
            $size -= 2;
        } while (count($lines) > 4 && $size > 40);
        $size += 2;
        $y = self::PAD + 60 + $size * 0.95;
        foreach ($lines as $line) {
            $this->text($line, self::PAD, $y, $size, -$size * 0.04, '#111113', $this->boldFont);
            $y += $size * 1.02;
        }

        // Caption and domain at the bottom
        $this->roundRect(self::PAD, self::HEIGHT - self::PAD - 12, 10, 10, 3, '#f95083');
        $this->text($caption, self::PAD + 22, self::HEIGHT - self::PAD - 2, 22, 0, '#5d5d63', $this->regularFont);

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
        $width = fn (string $str) => ($b = imagettfbbox($this->pt($px), 0, $font, $str))[2] - $b[0];

        return ($width("H{$ch}H") - $width('HH')) / self::SCALE;
    }

    private function measure(string $text, float $px, float $tracking, string $font): float
    {
        $w = 0;
        foreach (mb_str_split($text) as $ch) {
            $w += $this->advance($ch, $px, $font) + $tracking;
        }

        return $w - $tracking;
    }

    private function text(string $text, float $x, float $baseline, float $px, float $tracking, string $hex, string $font): void
    {
        $color = $this->color($hex);
        foreach (mb_str_split($text) as $ch) {
            if ($ch !== ' ') {
                imagettftext($this->img, $this->pt($px), 0, (int) round($x * self::SCALE), (int) round($baseline * self::SCALE), $color, $font, $ch);
            }
            $x += $this->advance($ch, $px, $font) + $tracking;
        }
    }

    /** @return list<string> */
    private function wrap(string $text, float $px, float $max, string $font, float $tracking): array
    {
        $rows = [];
        $current = '';
        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $try = $current === '' ? $word : "{$current} {$word}";
            if ($current !== '' && $this->measure($try, $px, $tracking, $font) > $max) {
                $rows[] = $current;
                $current = $word;
            } else {
                $current = $try;
            }
        }
        $rows[] = $current;

        return $rows;
    }

    private function roundRect(float $x, float $y, float $w, float $h, float $r, string $hex): void
    {
        $s = self::SCALE;
        [$x, $y, $w, $h, $r] = array_map(fn ($v) => (int) round($v * $s), [$x, $y, $w, $h, $r]);
        $c = $this->color($hex);
        imagefilledrectangle($this->img, $x + $r, $y, $x + $w - $r - 1, $y + $h - 1, $c);
        imagefilledrectangle($this->img, $x, $y + $r, $x + $w - 1, $y + $h - $r - 1, $c);
        foreach ([[$x + $r, $y + $r], [$x + $w - $r - 1, $y + $r], [$x + $r, $y + $h - $r - 1], [$x + $w - $r - 1, $y + $h - $r - 1]] as [$cx, $cy]) {
            imagefilledellipse($this->img, $cx, $cy, $r * 2, $r * 2, $c);
        }
    }

    /** Repaints the background outside the card's rounded corners, trimming the photo to the card. */
    private function clipCorners(float $x, float $y, float $w, float $h, float $r): void
    {
        $s = self::SCALE;
        [$x, $y, $w, $h, $r] = array_map(fn ($v) => (int) round($v * $s), [$x, $y, $w, $h, $r]);
        $bg = $this->color(self::BG);
        $corners = [[$x, $y, $x + $r, $y + $r], [$x + $w - $r, $y, $x + $w - $r - 1, $y + $r], [$x, $y + $h - $r, $x + $r, $y + $h - $r - 1], [$x + $w - $r, $y + $h - $r, $x + $w - $r - 1, $y + $h - $r - 1]];
        foreach ($corners as [$x0, $y0, $cx, $cy]) {
            for ($px = $x0; $px < $x0 + $r; $px++) {
                for ($py = $y0; $py < $y0 + $r; $py++) {
                    if (($px - $cx) ** 2 + ($py - $cy) ** 2 > $r ** 2) {
                        imagesetpixel($this->img, $px, $py, $bg);
                    }
                }
            }
        }
    }

    private function color(string $hex): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocate($this->img, $r, $g, $b);
    }
}
