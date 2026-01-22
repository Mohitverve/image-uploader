<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;

$db = Database::connect();

$stmt = $db->query("
    SELECT 
        id,
        original_name,
        original_mime,
        width,
        height,
        size_kb,
        cdn_url,
        created_at
    FROM images
    ORDER BY id DESC
");

$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<h3 class="mb-4">Uploaded Images</h3>

<?php if (empty($images)): ?>
    <div class="alert alert-info">
        No images uploaded yet.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th style="width:140px;">Preview</th>
                    <th>Original Name</th>
                    <th style="width:120px;">Dimensions</th>
                    <th style="width:100px;">Size (KB)</th>
                    <th style="width:180px;">Uploaded</th>
                    <th style="width:140px;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($images as $img): ?>
                <tr>
                    <td><?= (int) $img['id'] ?></td>

                    <!-- CDN Preview -->
                    <td>
                        <img
                            src="<?= htmlspecialchars($img['cdn_url']) ?>"
                            alt="Preview"
                            class="img-fluid border rounded"
                            style="max-height:80px; max-width:120px;"
                            loading="lazy"
                        >
                    </td>

                    <td>
                        <?= htmlspecialchars($img['original_name']) ?>
                    </td>

                    <td>
                        <?= (int) $img['width'] ?> × <?= (int) $img['height'] ?>
                    </td>

                    <td>
                        <?= (int) $img['size_kb'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($img['created_at']) ?>
                    </td>

                    <!-- FORCED DOWNLOAD (via PHP) -->
                    <td>
                      <a
  href="<?= htmlspecialchars(
        preg_replace(
            '/\/upload\//',
            '/upload/fl_attachment/',
            $img['cdn_url']
        )
    ) ?>"
  class="btn btn-sm btn-outline-primary"
>
    Download
</a>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title   = 'Admin - Images';

require __DIR__ . '/../../templates/layout.php';
