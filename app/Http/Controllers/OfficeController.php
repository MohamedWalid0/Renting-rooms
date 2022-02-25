<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Repository\Interfaces\OfficeRepositoryInterface;

class OfficeController extends Controller
{

    protected $office;

    public function __construct(OfficeRepositoryInterface $office)
    {
        $this->office = $office;
    }

    public function index()
    {
        return $this->office->index();
    }

    public function show(Office $office){

        return $this->office->show($office);

    }



    public function create(){

        return $this->office->create();

    }




    public function update(Office $office){

        return $this->office->update($office);

    }



    public function delete(Office $office)
    {
        return $this->office->delete($office);
    }


}
