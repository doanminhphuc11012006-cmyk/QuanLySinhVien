<?php require 'config.php'; admin_only(); $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals(csrf(), $_POST['t'] ?? '')) {
    if (isset($_POST['del'])) { db()->prepare('DELETE FROM songs WHERE id=?')->execute([(int)$_POST['del']]); $msg = 'Đã xóa bài hát.'; }
    else {
        $title = trim($_POST['title'] ?? ''); $artist = trim($_POST['artist'] ?? ''); $path = trim($_POST['url'] ?? '');
        $f = $_FILES['mp3'] ?? null;
        if ($f && $f['error'] === UPLOAD_ERR_OK) {
            if (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'mp3' || $f['size'] > 10 * 1024 * 1024) $path = '';
            else { $name = bin2hex(random_bytes(8)) . '.mp3'; move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $name); $path = 'uploads/' . $name; }
        }
        if ($title === '' || $artist === '' || $path === '') $msg = 'Cần tên bài, nghệ sĩ và một file .mp3 (tối đa 10MB) hoặc link nhạc.';
        else { db()->prepare('INSERT INTO songs(title,artist,file_path) VALUES(?,?,?)')->execute([$title, $artist, $path]); $msg = 'Đã thêm bài hát.'; }
    }
}
$songs = db()->query('SELECT * FROM songs ORDER BY id DESC')->fetchAll();
head('Quản lý bài hát - Sóng'); ?>
<h1>Quản lý bài hát</h1><?php if ($msg): ?><p class="err"><?= e($msg) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="box"><input type="hidden" name="t" value="<?= csrf() ?>">
<input name="title" placeholder="Tên bài hát" required><input name="artist" placeholder="Nghệ sĩ" required>
<input name="url" placeholder="Link nhạc (nếu không tải file)"><input type="file" name="mp3" accept=".mp3">
<button>Thêm bài hát</button></form>
<ol class="tracks"><?php foreach ($songs as $s): ?><li class="row"><span class="t"><b><?= e($s['title']) ?></b><small><?= e($s['artist']) ?></small></span>
<form method="post"><input type="hidden" name="t" value="<?= csrf() ?>"><button name="del" value="<?= $s['id'] ?>" onclick="return confirm('Xóa bài này?')">Xóa</button></form></li>
<?php endforeach; ?></ol><?php foot();
