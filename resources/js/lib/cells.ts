/**
 * The 200-cell system: one cell per project, laid out on an 11-column grid
 * (one column per year, 2016–2026). Every graphic on the site is a layout of
 * these same 200 cells.
 */

export const COLS = 11;
export const ROWS = 19;
export const SLOTS = COLS * ROWS; // 209: nine slots always stay empty
export const COUNT = 200;

export type LayoutName =
  | "all"
  | "200"
  | "11+"
  | "food"
  | "telecom"
  | "trade"
  | "employment"
  | "agencies";

type Bitmap = boolean[];

const blank = (): Bitmap => new Array(SLOTS).fill(false);
const set = (b: Bitmap, x: number, y: number) => {
  if (x >= 0 && x < COLS && y >= 0 && y < ROWS) b[y * COLS + x] = true;
};

/** Every project, filled year column by year column, bottom up. */
function allLayout(): Bitmap {
  const b = blank();
  let n = 0;
  for (let x = 0; x < COLS && n < COUNT; x++) {
    for (let y = ROWS - 1; y >= 0 && n < COUNT; y--, n++) set(b, x, y);
  }
  return b;
}

/** Tall pixel numerals, 3 cells wide, 15 cells high. */
function glyph(b: Bitmap, ch: string, x0: number, y0 = 2, h = 15) {
  const mid = y0 + (h - 1) / 2;
  const bottom = y0 + h - 1;
  const row = (y: number) => [0, 1, 2].forEach((i) => set(b, x0 + i, y));
  const col = (x: number, from: number, to: number) => {
    for (let y = from; y <= to; y++) set(b, x, y);
  };
  switch (ch) {
    case "0":
      row(y0); row(bottom); col(x0, y0, bottom); col(x0 + 2, y0, bottom);
      break;
    case "1":
      col(x0 + 1, y0, bottom); set(b, x0, y0 + 1); row(bottom);
      break;
    case "2":
      row(y0); col(x0 + 2, y0, mid); row(mid); col(x0, mid, bottom); row(bottom);
      break;
    case "+":
      col(x0 + 1, mid - 3, mid + 3); row(mid);
      break;
  }
}

function numeral(text: string): Bitmap {
  const b = blank();
  [...text].forEach((ch, i) => glyph(b, ch, i * 4));
  return b;
}

/** Pictograms drawn as 11-wide pixel art, centred vertically. */
function art(lines: string[]): Bitmap {
  const b = blank();
  const top = Math.floor((ROWS - lines.length) / 2);
  lines.forEach((line, y) =>
    [...line].forEach((c, x) => c === "#" && set(b, x, top + y)),
  );
  return b;
}

const PICTOGRAMS: Record<string, string[]> = {
  // A burger, stacked
  food: [
    "...#####...",
    ".#########.",
    "###########",
    "###########",
    "...........",
    "#.#.#.#.#.#",
    "###########",
    "...........",
    "###########",
    "###########",
    "...........",
    "###########",
    ".#########.",
  ],
  // Signal strength
  telecom: [
    ".........##",
    ".........##",
    ".........##",
    ".........##",
    "......##.##",
    "......##.##",
    "......##.##",
    "......##.##",
    "...##.##.##",
    "...##.##.##",
    "...##.##.##",
    "...##.##.##",
    "##.##.##.##",
    "##.##.##.##",
    "##.##.##.##",
    "##.##.##.##",
  ],
  // Stacked shipping containers
  trade: [
    "###########",
    "#.#.#.#.#.#",
    "#.#.#.#.#.#",
    "###########",
    "...........",
    "###########",
    "#.#.#.#.#.#",
    "#.#.#.#.#.#",
    "###########",
    "...........",
    "###########",
    "#.#.#.#.#.#",
    "#.#.#.#.#.#",
    "###########",
  ],
  // A briefcase
  employment: [
    "...#####...",
    "...#...#...",
    "###########",
    "#.........#",
    "#.........#",
    "#.........#",
    "####.#.####",
    "#...###...#",
    "#.........#",
    "#.........#",
    "#.........#",
    "###########",
  ],
  // A browser window and a cursor
  agencies: [
    "###########",
    "#.#.#.....#",
    "###########",
    "#.........#",
    "#.#####...#",
    "#.........#",
    "#.###.....#",
    "#.........#",
    "###########",
    "...........",
    "......#....",
    "......##...",
    "......###..",
    "......####.",
    "......##...",
    "......#.#..",
  ],
};

const cache = new Map<LayoutName, Bitmap>();

export function layout(name: LayoutName): Bitmap {
  let b = cache.get(name);
  if (!b) {
    if (name === "all") b = allLayout();
    else if (name === "200" || name === "11+") b = numeral(name);
    else b = art(PICTOGRAMS[name]);
    cache.set(name, b);
  }
  return b;
}

export type Target = { slot: number; on: boolean };

/**
 * Assigns the 200 cells to slots for a layout. Built cells always come first
 * and are sorted in reading order (column by column), so between layouts the
 * same cells travel and regroup into the new shape.
 */
export function targets(name: LayoutName): Target[] {
  const b = layout(name);
  const order = (s: number) => (s % COLS) * ROWS + (ROWS - 1 - Math.floor(s / COLS));
  const on: number[] = [];
  const off: number[] = [];
  for (let s = 0; s < SLOTS; s++) (b[s] ? on : off).push(s);
  on.sort((a, c) => order(a) - order(c));
  off.sort((a, c) => order(a) - order(c));

  const onCount = Math.min(on.length, COUNT);
  return [
    ...on.slice(0, onCount).map((slot) => ({ slot, on: true })),
    ...off.slice(0, COUNT - onCount).map((slot) => ({ slot, on: false })),
  ];
}
