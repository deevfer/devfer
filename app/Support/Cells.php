<?php

namespace App\Support;

/**
 * PHP port of the "11+" numeral from resources/js/lib/cells.ts, used to draw
 * the cell grid on the Open Graph image. Keep both in sync.
 */
final class Cells
{
    public const COLS = 11;

    public const ROWS = 19;

    /** @return array<int, bool> slot index => built */
    public static function numeral(string $text): array
    {
        $b = [];
        foreach (mb_str_split($text) as $i => $ch) {
            self::glyph($b, $ch, $i * 4);
        }

        return $b;
    }

    /** @param array<int, bool> $b */
    private static function glyph(array &$b, string $ch, int $x0, int $y0 = 2, int $h = 15): void
    {
        $mid = $y0 + intdiv($h - 1, 2);
        $bottom = $y0 + $h - 1;
        $set = function (int $x, int $y) use (&$b): void {
            if ($x >= 0 && $x < self::COLS && $y >= 0 && $y < self::ROWS) {
                $b[$y * self::COLS + $x] = true;
            }
        };
        $row = function (int $y) use ($set, $x0): void {
            foreach ([0, 1, 2] as $i) {
                $set($x0 + $i, $y);
            }
        };
        $col = function (int $x, int $from, int $to) use ($set): void {
            for ($y = $from; $y <= $to; $y++) {
                $set($x, $y);
            }
        };

        switch ($ch) {
            case '0':
                $row($y0); $row($bottom); $col($x0, $y0, $bottom); $col($x0 + 2, $y0, $bottom);
                break;
            case '1':
                $col($x0 + 1, $y0, $bottom); $set($x0, $y0 + 1); $row($bottom);
                break;
            case '2':
                $row($y0); $col($x0 + 2, $y0, $mid); $row($mid); $col($x0, $mid, $bottom); $row($bottom);
                break;
            case '+':
                $col($x0 + 1, $mid - 3, $mid + 3); $row($mid);
                break;
        }
    }
}
