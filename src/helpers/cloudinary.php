<?php

use Cloudinary\Api\Upload\UploadApi;

function uploadToCloudinary(string $filePath, string $publicId): string
{
    $upload = new UploadApi();

    $response = $upload->upload($filePath, [
        'folder'        => 'images/' . date('Y/m'),
        'public_id'     => $publicId,
        'resource_type' => 'image',
        'overwrite'     => true,
    ]);

    // ApiResponse object → array
    $data = $response->getArrayCopy();

    return $data['secure_url'];
}
