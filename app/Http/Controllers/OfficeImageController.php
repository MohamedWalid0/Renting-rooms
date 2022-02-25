<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfficeImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use App\Repository\Interfaces\OfficeImageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OfficeImageController extends Controller
{

    protected $officeImage;

    public function __construct(OfficeImageRepositoryInterface $officeImage)
    {
        $this->officeImage = $officeImage;
    }


    public function store(OfficeImageRequest $request , Office $office){

        return $this->officeImage->store($request , $office) ;

    }

    public function delete(Office $office, Image $image){

        return $this->officeImage->delete( $office , $image ) ;


    }



}
