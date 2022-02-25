<?php


namespace App\Repository;
use App\Repository\Interfaces\OfficeImageRepositoryInterface;


use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class OfficeImageRepository implements OfficeImageRepositoryInterface {

    public function store($request ,  Office $office): JsonResource
    {
        abort_if( auth()->user()->cannot('update', $office) , 403) ;

        $image = $office->images()->create([
            'path' => request()->file('image')->storePublicly('/')
        ]);

        return ImageResource::make($image);

    }

    public function delete(Office $office, Image $image)
    {
        abort_if( auth()->user()->cannot('update', $office) , 403) ;

        throw_if($office->images()->count() == 1,
            ValidationException::withMessages(['image' => 'Cannot delete the only image.'])
        );

        throw_if($office->featured_image_id == $image->id,
            ValidationException::withMessages(['image' => 'Cannot delete the featured image.'])
        );

        Storage::delete($image->path);

        $image->delete();
    }


}
