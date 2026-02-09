<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

trait HandlesImageUpload
{
    /**
     * Handle image upload from the 'uploaded_images' field.
     *
     * @param Request $request
     * @param mixed $model The model to attach the image to (must use InteractsWithMedia)
     * @param string $collection The media collection name
     * @return bool True if image was uploaded, false otherwise
     */
    public function handleImageUpload(Request $request, $model, string $collection = 'default'): bool
    {
        // Check if the 'uploaded_images' field exists and is not empty
        if (!empty($request->uploaded_images) && isset($request->uploaded_images[0])) {
            // Get the first image filename from the uploaded_images array
            $imageName = $request->uploaded_images[0];

            // Define the full path where the image is stored
            $pathToMedia = public_path('uploads/' . $imageName);

            // Check if the image file exists at the specified path
            if (file_exists($pathToMedia)) {
                // Clear the existing image from the media collection (for update)
                if ($model->hasMedia($collection)) {
                    $model->clearMediaCollection($collection);
                }

                // Add the new image to the media collection
                $model->addMedia($pathToMedia)->toMediaCollection($collection);

                return true;
            }

            return false;
        }

        return false;
    }
}
