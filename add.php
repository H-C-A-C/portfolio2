<?php
require_once 'db.php';

$title = trim($_POST['title'] ?? '');
$due_date = $_POST['due_date'] ?? ''; // YYYY-MM-DD
$due_time = $_POST['due_time'] ?? ''; // HH:MM

$due_at = null;
if ($due_date !== '') {
  // 時刻未指定なら 23:59 にする（好みで 00:00 でもOK）
  if ($due_time === '') $due_time = '23:59';
  $due_at = $due_date . ' ' . $due_time . ':00';
}

if ($title !== '') {
  $stmt = $pdo->prepare("INSERT INTO tasks (title, due_at, is_done) VALUES (:title, :due_at, 0)");
  $stmt->execute([
    ':title' => $title,
    ':due_at' => $due_at
  ]);
}

header('Location: index.php');
exit;