<?php


namespace App\Repository\Interfaces;

use App\Models\Image;
use App\Models\Office;

interface OfficeImageRepositoryInterface
{

    public function store($request , Office $office);
    public function delete(Office $office , Image $image);

}
