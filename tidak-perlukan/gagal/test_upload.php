<?php
if (isset($_FILES['foto'])) {
    echo "<pre>";
    print_r($_FILES['foto']);
    echo "</pre>";
    
    $target = __DIR__ . '/test_' . $_FILES['foto']['name'];
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        echo "SUKSES! File tersimpan di: " . $target;
    } else {
        echo "GAGAL upload!";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foto">
    <button type="submit">Upload Test</button>
</form>