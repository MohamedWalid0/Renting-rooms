<?php


namespace App\Repository\Interfaces;

use App\Models\Office;

interface OfficeRepositoryInterface
{

    public function index();
    public function show(Office $office);
    public function create();
    public function update(Office $office);
    public function delete(Office $office);


}
