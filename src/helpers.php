<?php

function abort(string $message): void
{
    header(
        'Location: /image-uploader/public/?error=' . urlencode($message)
    );
    exit;
}

function isValidImage(string $tmpFile): bool
{
    return getimagesize($tmpFile) !== false;
}
