<?php
require_once 'db.php';

$id = (int)($_POST['id'] ?? 0);
// チェックされて送られてくると "1"、外すと未送信になるので 0
$is_done = isset($_POST['is_done']) ? 1 : 0;

if ($id > 0) {
  $stmt = $pdo->prepare("UPDATE tasks SET is_done = :is_done WHERE id = :id");
  $stmt->execute([
    ':is_done' => $is_done,
    ':id' => $id
  ]);
}

header('Location: index.php');
exit;