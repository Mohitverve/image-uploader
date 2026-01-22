<?php
session_start();

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
$image   = $_SESSION['image'] ?? null;

unset($_SESSION['success'], $_SESSION['error'], $_SESSION['image']);

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">

        <div class="card shadow-sm">
            <div class="card-body">

                <h4 class="mb-3">Upload Image</h4>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="upload.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Select image</label>
                        <input type="file" name="image" class="form-control" required>
                    </div>

                    <button class="btn btn-primary">Upload & Optimise</button>
                </form>

                <?php if ($image): ?>
                    <hr>

                    <h5>Optimised Image</h5>

                   <?php if (!empty($_SESSION['image']['cdn_url'])): ?>
    <img
        src="<?= htmlspecialchars($_SESSION['image']['cdn_url']) ?>"
        class="img-fluid mb-3 border"
        alt="Optimised image"
    >
<?php endif; ?>

                    <ul class="list-group">
                        <li class="list-group-item">
                            Dimensions: <?= $image['width'] ?> × <?= $image['height'] ?>
                        </li>
                        <li class="list-group-item">
                            Size: <?= round($image['size_kb'], 1) ?> KB
                        </li>
                    </ul>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Upload Image';
require __DIR__ . '/../templates/layout.php';
