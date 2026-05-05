<?php
/**
 * Plugin Name: Basic Tetris Game (Canvas)
 * Description: Adds a modernized Tetris canvas game via shortcode [tetris_game].
 * Version: 2.0
 */

if (!defined('ABSPATH')) exit;

function tetris_game_shortcode() {
  $js_path = plugin_dir_path(__FILE__) . 'tetris.js';
  $js_ver  = file_exists($js_path) ? filemtime($js_path) : '2.0';

  wp_enqueue_script(
    'tetris-game-js',
    plugins_url('tetris.js', __FILE__),
    array(),
    $js_ver,
    true
  );

  ob_start();
  ?>
  <div class="tetris-v2" id="tetris-v2">
    <div class="tetris-layout">
      <div class="tetris-stage" id="tetris-stage">
        <canvas width="320" height="640" id="tetris-game"></canvas>

        <div class="tetris-overlay" id="tetris-overlay">
          <div class="tetris-card">
            <h2 class="tetris-title" id="tetris-title">Tetris</h2>

            <label class="tetris-label" for="tetris-difficulty">Difficulty</label>
            <select id="tetris-difficulty" class="tetris-select">
              <option value="easy">Easy</option>
              <option value="normal" selected>Normal</option>
              <option value="hard">Hard</option>
            </select>

            <button class="tetris-primary" id="tetris-play-btn" type="button">Play Now</button>

            <div class="tetris-gameover" id="tetris-gameover" style="display:none;">
              <p class="tetris-subtext" id="tetris-final"></p>

              <label class="tetris-label" for="tetris-name">Your name</label>
              <input id="tetris-name" class="tetris-input" type="text" maxlength="16" placeholder="Damon" />

              <button class="tetris-secondary" id="tetris-save-score" type="button">Save Score</button>
              <button class="tetris-primary" id="tetris-restart-btn" type="button">Restart</button>
            </div>

            <details class="tetris-rules">
              <summary>Rules & controls</summary>
              <div class="tetris-rules-body">
                <p><strong>Goal:</strong> Clear lines by filling rows with blocks. Each cleared line gives points. The game speeds up as you level up.</p>
                <ul>
                  <li><strong>Move:</strong> Left / Right</li>
                  <li><strong>Rotate:</strong> Up / Rotate button</li>
                  <li><strong>Soft drop:</strong> Down (faster fall)</li>
                  <li><strong>Hard drop:</strong> Space / Drop button (instant)</li>
                  <li><strong>Hold:</strong> C / Hold button (swap current piece)</li>
                </ul>
              </div>
            </details>
          </div>
        </div>
      </div>

      <aside class="tetris-panel">
        <div class="tetris-stats">
          <div class="tetris-stat"><span>Score</span><strong id="tetris-score">0</strong></div>
          <div class="tetris-stat"><span>Level</span><strong id="tetris-level">1</strong></div>
          <div class="tetris-stat"><span>Lines</span><strong id="tetris-lines">0</strong></div>
        </div>

        <div class="tetris-previews">
          <div class="tetris-preview">
            <span>Next</span>
            <canvas width="128" height="128" id="tetris-next"></canvas>
          </div>
          <div class="tetris-preview">
            <span>Hold</span>
            <canvas width="128" height="128" id="tetris-hold"></canvas>
          </div>
        </div>

        <div class="tetris-highscores">
          <h3>Top Scores</h3>
          <ol id="tetris-highscore-list"></ol>
        </div>
      </aside>
    </div>

    <div class="tetris-controls" id="tetris-controls" aria-label="Tetris controls">
      <button type="button" class="tetris-btn" data-action="left">Left</button>
      <button type="button" class="tetris-btn" data-action="rotate">Rotate</button>
      <button type="button" class="tetris-btn" data-action="right">Right</button>
      <button type="button" class="tetris-btn" data-action="down">Down</button>
      <button type="button" class="tetris-btn" data-action="drop">Drop</button>
      <button type="button" class="tetris-btn" data-action="hold">Hold</button>
    </div>
  </div>

  <style>
    .tetris-v2 { padding: 20px 0; }

    .tetris-layout {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
      justify-items: center;
      align-items: start;
      padding: 0 12px;
    }

    @media (min-width: 900px) {
      .tetris-layout {
        grid-template-columns: auto 280px;
        justify-content: center;
      }
    }

    .tetris-stage {
  position: relative;
  width: min(320px, 92vw);
  margin: 8px auto;
    }

    @media (max-width: 760px) {
  #tetris-next, #tetris-hold {
    width: 96px;
    height: 96px;
  }

  .tetris-box, .tetris-stats {
    padding: 8px;
  }

  .tetris-btn {
    min-width: 90px;
    padding: 10px 12px;
  }
}


    @media (max-width: 760px) {
        .tetris-stage {
            width: min(300px, 92vw);
        }
    }

    /* Keep the canvas proportional but stop it eating the whole screen */
    #tetris-game {
        width: 100%;
        height: auto;
        max-height: 62vh;
        display: block;
        border: 1px solid #fff;
        background: #000;
        outline: none;
        touch-action: none;
        image-rendering: pixelated;
    }


    .tetris-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0,0,0,0.55);
      z-index: 10;
    }

    .tetris-card {
      width: min(320px, 92%);
      background: rgba(0,0,0,0.75);
      border: 1px solid rgba(255,255,255,0.25);
      padding: 14px;
      border-radius: 10px;
      color: #fff;
      text-align: center;
    }

    .tetris-title { margin: 0 0 8px; font-size: 26px; }

    .tetris-label { display:block; text-align:left; font-size: 12px; opacity:0.85; margin: 10px 0 6px; }
    .tetris-select, .tetris-input {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid rgba(255,255,255,0.2);
      background: rgba(20,20,20,0.9);
      color: #fff;
      box-sizing: border-box;
    }

    .tetris-primary, .tetris-secondary {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 2px solid #fff;
      cursor: pointer;
      margin-top: 10px;
      touch-action: manipulation;
    }
    .tetris-primary { background: #111; color: #fff; }
    .tetris-secondary { background: rgba(255,255,255,0.12); color: #fff; border-color: rgba(255,255,255,0.6); }

    .tetris-rules { margin-top: 10px; text-align:left; }
    .tetris-rules summary { cursor: pointer; }
    .tetris-rules-body { font-size: 13px; opacity: 0.9; }

    .tetris-panel {
      width: min(320px, 86vw);
      color: #111;
      background: #f8f8f8;
      border: 1px solid #e6e6e6;
      border-radius: 10px;
      padding: 12px;
      box-sizing: border-box;
    }
    @media (min-width: 900px) {
      .tetris-panel { width: 280px; }
    }

    .tetris-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 10px;
    }
    .tetris-stat span { display:block; font-size: 12px; opacity:0.7; }
    .tetris-stat strong { font-size: 18px; }

    .tetris-previews {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin: 10px 0 12px;
      justify-items: center;
    }
    .tetris-preview span { display:block; font-size: 12px; opacity:0.8; margin-bottom: 6px; }
    #tetris-next, #tetris-hold { background:#fff; border: 1px solid #ddd; border-radius: 8px; }

    .tetris-highscores h3 { margin: 0 0 8px; font-size: 16px; }
    .tetris-highscores ol { margin: 0; padding-left: 18px; font-size: 13px; }
    .tetris-highscores li { margin: 4px 0; }

    .tetris-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        width: min(520px, 96vw);
        margin: 0 auto;
    }

    /* Mobile: stick controls to bottom of screen */
    @media (max-width: 900px) {
        .tetris-controls {
            position: sticky;
            bottom: calc(env(safe-area-inset-bottom) + 8px);
            background: rgba(255, 255, 255, 0.96);
            padding: 10px 10px calc(env(safe-area-inset-bottom) + 10px);
            border-top: 1px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            z-index: 999;
        }
    }

    /* Desktop: hide mobile controls */
    @media (min-width: 900px) {
        .tetris-controls {
            display: none;
        }
    }
    
    body.tetris-no-scroll {
        overflow: hidden;
        touch-action: none;
    }


    .tetris-btn {
      font-size: 16px;
      padding: 10px 14px;
      border: 1px solid #333;
      background: #f5f5f5;
      cursor: pointer;
      border-radius: 10px;
      min-width: 88px;
      user-select: none;
      -webkit-user-select: none;
      touch-action: manipulation;
    }

    @media (min-width: 900px) {
      .tetris-controls { display: none; }
    }
  </style>
  <?php
  return ob_get_clean();
}

add_shortcode('tetris_game', 'tetris_game_shortcode');
add_shortcode('tetris-game', 'tetris_game_shortcode');
