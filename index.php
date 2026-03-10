<?php
declare(strict_types=1);

function readIntParam(string $name, int $default, int $min, int $max): int
{
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        return $default;
    }

    return max($min, min($max, $value));
}

$rows = readIntParam('rows', 10, 6, 18);
$cols = readIntParam('cols', 7, 4, 12);

$initialState = [];
for ($row = 0; $row < $rows; $row++) {
    $stateRow = [];
    for ($col = 0; $col < $cols; $col++) {
        $stateRow[] = 0;
    }
    $initialState[] = $stateRow;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tōltēcah</title>
  <meta name="description" content="Play Tōltēcah, a free casual browser puzzle game where rotating jade coins trigger chain reactions. Fast rounds, satisfying combos, and Mayan-inspired temple vibes.">
  <meta name="keywords" content="casual browser game, chain reaction game, puzzle game online, free web game, strategy puzzle, mayan game, jade temple game, rotator puzzle">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
  <link rel="canonical" href="https://blahpunk.com/tolteca/">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Tōltēcah | Casual Browser Chain Reaction Game">
  <meta property="og:description" content="Rotate coins, trigger combos, and chase high chains in this free Mayan-inspired casual browser puzzle game.">
  <meta property="og:url" content="https://blahpunk.com/tolteca/">
  <meta property="og:image" content="https://blahpunk.com/tolteca/assets/image.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Tōltēcah | Casual Browser Chain Reaction Game">
  <meta name="twitter:description" content="A free, fast, chain-reaction puzzle for browser players with a dark jade temple aesthetic.">
  <meta name="twitter:image" content="https://blahpunk.com/tolteca/assets/image.png">
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "VideoGame",
      "name": "Tōltēcah",
      "url": "https://blahpunk.com/tolteca/",
      "image": "https://blahpunk.com/tolteca/assets/image.png",
      "description": "Tōltēcah is a free casual browser puzzle game where rotating jade coins trigger chain reactions in a Mayan-inspired temple setting.",
      "genre": ["Puzzle", "Casual", "Strategy"],
      "playMode": "SinglePlayer",
      "applicationCategory": "Game",
      "operatingSystem": "Any",
      "inLanguage": "en",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      }
    }
  </script>
  <style>
    :root {
      --ink: #cbdbc8;
      --panel: rgba(18, 46, 41, 0.88);
      --panel-edge: #355e55;
      --tile-1: #0f2d29;
      --tile-2: #183d36;
      --tile-3: #2a5a50;
      --tile-line: rgba(186, 211, 185, 0.2);
      --gold: #b18f47;
      --stone: #1b3732;
    }

    * {
      box-sizing: border-box;
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    body {
      margin: 0;
      min-height: 100vh;
      font-family: "Cinzel", "Palatino Linotype", "Book Antiqua", serif;
      color: var(--ink);
      background:
        radial-gradient(circle at 14% 12%, rgba(120, 169, 146, 0.13), rgba(0, 0, 0, 0) 45%),
        radial-gradient(circle at 80% 80%, rgba(126, 94, 44, 0.16), rgba(0, 0, 0, 0) 34%),
        repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.02) 0 3px, rgba(0, 0, 0, 0.03) 3px 8px),
        linear-gradient(155deg, #081c19, #0f322d 46%, #224c43);
      display: grid;
      place-items: center;
      padding: 24px;
    }

    .game-shell {
      width: min(760px, 96vw);
      border-radius: 24px;
      background: var(--panel);
      box-shadow: 0 22px 48px rgba(0, 0, 0, 0.45), inset 0 0 0 1px rgba(190, 213, 180, 0.1);
      border: 1px solid var(--panel-edge);
      padding: 20px;
      position: relative;
    }

    .game-shell::before {
      content: "";
      position: absolute;
      inset: 10px;
      border: 1px solid rgba(177, 143, 71, 0.4);
      border-radius: 16px;
      pointer-events: none;
    }

    .controls {
      display: grid;
      grid-template-columns: repeat(4, minmax(120px, 1fr));
      gap: 8px;
      margin-bottom: 14px;
    }

    .stat {
      background:
        linear-gradient(180deg, rgba(26, 57, 50, 0.95), rgba(17, 42, 37, 0.95));
      border: 1px solid rgba(173, 146, 77, 0.58);
      border-radius: 12px;
      padding: 10px 12px;
      box-shadow: inset 0 0 0 1px rgba(198, 214, 189, 0.08);
    }

    .label {
      display: block;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.09em;
      color: rgba(198, 212, 189, 0.76);
    }

    .value {
      font-size: 1.45rem;
      font-weight: 600;
      line-height: 1.15;
      margin-top: 3px;
      display: block;
      color: #d7e8d0;
      text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
    }

    .board {
      --cols: 10;
      display: grid;
      grid-template-columns: repeat(var(--cols), minmax(24px, 1fr));
      gap: 0;
      width: min(520px, 100%);
      margin-inline: auto;
      background:
        linear-gradient(180deg, var(--tile-2), var(--tile-1));
      border: 1px solid rgba(173, 146, 77, 0.55);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: inset 0 0 0 1px rgba(203, 221, 193, 0.1), 0 12px 22px rgba(0, 0, 0, 0.3);
    }

    .board.busy {
      pointer-events: none;
    }

    .bubble {
      border: none;
      background: transparent;
      width: 100%;
      aspect-ratio: 1 / 1;
      padding: 0;
      cursor: pointer;
      position: relative;
      animation: tile-enter 420ms cubic-bezier(.17, .84, .44, 1) both;
      animation-delay: calc(var(--i) * 16ms);
    }

    .bubble::before {
      content: "";
      position: absolute;
      inset: 0;
      border-right: 1px solid var(--tile-line);
      border-bottom: 1px solid var(--tile-line);
      background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.02), rgba(0, 0, 0, 0.06));
      pointer-events: none;
    }

    .bubble-img {
      width: 100%;
      height: 100%;
      display: block;
      transform: rotate(calc(var(--turn, 0) * 90deg));
      transform-origin: center;
      transition: transform 170ms cubic-bezier(.16, .88, .26, 1), filter 140ms ease;
      filter: drop-shadow(0 4px 7px rgba(1, 11, 9, 0.45));
      object-fit: contain;
      padding: 8%;
    }

    .bubble:hover .bubble-img {
      filter: drop-shadow(0 7px 10px rgba(0, 0, 0, 0.5));
    }

    .bubble.pulse .bubble-img {
      animation: pulse 230ms ease-out;
    }

    @keyframes pulse {
      0% {
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.45));
      }
      70% {
        filter: drop-shadow(0 10px 12px rgba(177, 143, 71, 0.4));
      }
      100% {
        filter: drop-shadow(0 4px 7px rgba(1, 11, 9, 0.45));
      }
    }

    @keyframes tile-enter {
      from {
        transform: translateY(16px) scale(0.8);
        opacity: 0;
      }
      to {
        transform: translateY(0) scale(1);
        opacity: 1;
      }
    }

    @media (max-width: 760px) {
      body {
        padding: 12px;
      }

      .game-shell {
        padding: 14px;
      }

      .controls {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
      }

      .value {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <h1 class="sr-only">Tōltēcah, a Mayan-inspired casual browser chain reaction puzzle game</h1>
  <p class="sr-only">
    Play free online and rotate coins to trigger combo chains, score points, and beat your best run.
  </p>
  <main class="game-shell">
    <section class="controls">
      <div class="stat">
        <span class="label">Score</span>
        <span id="score" class="value">0</span>
      </div>
      <div class="stat">
        <span class="label">Moves</span>
        <span id="moves" class="value">0</span>
      </div>
      <div class="stat">
        <span class="label">Last Chain</span>
        <span id="last-chain" class="value">0</span>
      </div>
      <div class="stat">
        <span class="label">Best Chain</span>
        <span id="best-chain" class="value">0</span>
      </div>
    </section>

    <div id="board" class="board" style="--cols: <?php echo $cols; ?>;">
      <?php
      $tileIndex = 0;
      for ($row = 0; $row < $rows; $row++):
          for ($col = 0; $col < $cols; $col++):
              $rotation = $initialState[$row][$col];
              ?>
              <button
                type="button"
                class="bubble"
                data-r="<?php echo $row; ?>"
                data-c="<?php echo $col; ?>"
                data-rotation="<?php echo $rotation; ?>"
                style="--rot: <?php echo $rotation; ?>; --turn: <?php echo $rotation; ?>; --i: <?php echo $tileIndex; ?>;"
                aria-label="Bubble <?php echo ($row + 1) . '-' . ($col + 1); ?>"
              >
                <img class="bubble-img" src="assets/image.png" alt="" aria-hidden="true">
              </button>
              <?php
              $tileIndex++;
          endfor;
      endfor;
      ?>
    </div>

  </main>

  <script>
    const rows = <?php echo $rows; ?>;
    const cols = <?php echo $cols; ?>;
    const startingState = <?php echo json_encode($initialState, JSON_THROW_ON_ERROR); ?>;

    const boardEl = document.getElementById("board");
    const scoreEl = document.getElementById("score");
    const movesEl = document.getElementById("moves");
    const lastChainEl = document.getElementById("last-chain");
    const bestChainEl = document.getElementById("best-chain");

    const cells = Array.from({ length: rows }, () => Array(cols));
    boardEl.querySelectorAll(".bubble").forEach((bubble) => {
      const row = Number(bubble.dataset.r);
      const col = Number(bubble.dataset.c);
      cells[row][col] = bubble;
    });

    const directions = [
      { dr: -1, dc: 0, side: 0 },
      { dr: 0, dc: 1, side: 1 },
      { dr: 1, dc: 0, side: 2 },
      { dr: 0, dc: -1, side: 3 }
    ];
    const oppositeSide = [2, 3, 0, 1];

    let state = cloneState(startingState);
    let turns = cloneState(startingState);
    let score = 0;
    let moves = 0;
    let bestChain = 0;
    let busy = false;

    function cloneState(grid) {
      return grid.map((row) => row.slice());
    }

    function sleep(ms) {
      return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function isReactive(rotation, side) {
      return side === ((1 + rotation) & 3) || side === ((2 + rotation) & 3);
    }

    function updateStatLine(lastChain) {
      scoreEl.textContent = String(score);
      movesEl.textContent = String(moves);
      lastChainEl.textContent = String(lastChain);
      bestChainEl.textContent = String(bestChain);
    }

    function rotateCell(row, col) {
      const bubble = cells[row][col];
      const nextRotation = (state[row][col] + 1) & 3;
      state[row][col] = nextRotation;
      turns[row][col] += 1;
      bubble.dataset.rotation = String(nextRotation);
      bubble.style.setProperty("--rot", String(nextRotation));
      bubble.style.setProperty("--turn", String(turns[row][col]));
      bubble.classList.remove("pulse");
      void bubble.offsetWidth;
      bubble.classList.add("pulse");
    }

    function setBusy(value) {
      busy = value;
      boardEl.classList.toggle("busy", value);
    }

    async function runMove(startRow, startCol) {
      if (busy) {
        return;
      }

      setBusy(true);
      moves += 1;

      let chainSize = 0;
      const queue = [{ row: startRow, col: startCol }];
      let reactions = 0;
      const maxReactions = rows * cols * 64;

      while (queue.length > 0 && reactions < maxReactions) {
        const cell = queue.shift();
        if (!cell) {
          break;
        }

        rotateCell(cell.row, cell.col);
        chainSize += 1;
        score += 1;
        reactions += 1;
        updateStatLine(chainSize);
        await sleep(140);

        const sourceRotation = state[cell.row][cell.col];
        for (const direction of directions) {
          const nextRow = cell.row + direction.dr;
          const nextCol = cell.col + direction.dc;
          if (nextRow < 0 || nextRow >= rows || nextCol < 0 || nextCol >= cols) {
            continue;
          }

          if (!isReactive(sourceRotation, direction.side)) {
            continue;
          }

          const touchingSide = oppositeSide[direction.side];
          if (!isReactive(state[nextRow][nextCol], touchingSide)) {
            continue;
          }

          queue.push({ row: nextRow, col: nextCol });
        }
      }

      if (reactions === maxReactions) {
        console.warn("Move stopped after hitting reaction safety limit.");
      }

      if (chainSize > bestChain) {
        bestChain = chainSize;
      }
      updateStatLine(chainSize);
      setBusy(false);
    }

    function redrawBoard() {
      for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
          const bubble = cells[row][col];
          const rotation = state[row][col];
          const turn = turns[row][col];
          bubble.dataset.rotation = String(rotation);
          bubble.style.setProperty("--rot", String(rotation));
          bubble.style.setProperty("--turn", String(turn));
          bubble.classList.remove("pulse");
        }
      }
    }

    boardEl.querySelectorAll(".bubble").forEach((bubble) => {
      bubble.addEventListener("click", () => {
        runMove(Number(bubble.dataset.r), Number(bubble.dataset.c));
      });
    });

    updateStatLine(0);
  </script>
</body>
</html>
