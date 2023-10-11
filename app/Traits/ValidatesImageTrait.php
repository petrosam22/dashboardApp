<?php
namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


trait ValidatesImageTrait
{
    protected function validateImage(UploadedFile $file , $folder){
        if($file === null || !$file->isValid()){
            throw new \Exception('No file was uploaded');
        }


        $validator = Validator::make(
            ['image' => $file],
            ['image' => 'required|image|max:2048']
        );

        $validator->validate();
        $fileName = $file->getClientOriginalName();

        $imagePath = $file->storeAs($folder, $fileName, 'public');

        return $imagePath;
    }
}
