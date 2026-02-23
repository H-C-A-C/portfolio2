<?php
require_once 'db.php';

// 期限優先（未完了→完了、期限あり→なし、期限早い→遅い、作成新しい順）
$stmt = $pdo->query("
  SELECT * FROM tasks
  ORDER BY is_done ASC, due_at IS NULL, due_at ASC, created_at DESC
");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function due_status_class($due_at, $is_done) {
  if ((int)$is_done === 1) return 'done';
  if (!$due_at) return 'no-due';

  $now = time();
  $due = strtotime($due_at);

  // その日の00:00/23:59で判定
  $todayStart = strtotime(date('Y-m-d 00:00:00', $now));
  $todayEnd   = strtotime(date('Y-m-d 23:59:59', $now));

  if ($due < $now) return 'overdue';
  if ($due >= $todayStart && $due <= $todayEnd) return 'today';

  $diffHours = ($due - $now) / 3600;
  if ($diffHours <= 48) return 'soon';  // 2日以内
  return 'later'; // それ以上
}

function format_due($due_at) {
  if (!$due_at) return '期限なし';
  return date('Y-m-d H:i', strtotime($due_at));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ToDo List</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>ToDo List</h1>

    <!-- 追加フォーム：日付はカレンダー、時刻は別 -->
    <form action="add.php" method="POST" class="task-form">
      <input type="text" name="title" placeholder="タスクを入力" required>

      <input type="date" name="due_date" class="due-date" aria-label="期限日">
      <input type="time" name="due_time" class="due-time" aria-label="期限時刻">

      <button type="submit">追加</button>
    </form>

    <ul class="task-list">
      <?php foreach ($tasks as $task): ?>
        <?php
          $cls = due_status_class($task['due_at'] ?? null, $task['is_done'] ?? 0);
          $is_done = (int)$task['is_done'] === 1;
        ?>
        <li class="task-item <?= h($cls) ?>">
          <!-- 完了チェック -->
          <form action="toggle.php" method="POST" class="toggle-form">
            <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
            <input
              type="checkbox"
              name="is_done"
              value="1"
              <?= $is_done ? 'checked' : '' ?>
              onchange="this.form.submit()"
              aria-label="完了"
            >
          </form>

          <!-- タスク本文 -->
          <div class="task-main">
            <div class="task-title"><?= h($task['title']) ?></div>
            <div class="task-meta">期限: <?= h(format_due($task['due_at'] ?? null)) ?></div>
          </div>

          <!-- 操作 -->
          <div class="task-actions">
            <a href="delete.php?id=<?= (int)$task['id'] ?>" class="delete-btn">削除</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- 凡例 -->
    <div class="legend">
      <span class="chip overdue">期限切れ</span>
      <span class="chip today">今日</span>
      <span class="chip soon">近日(48h)</span>
      <span class="chip later">余裕</span>
      <span class="chip no-due">期限なし</span>
    </div>
  </div>
</body>
</html>