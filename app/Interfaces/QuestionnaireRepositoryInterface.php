<?php


namespace App\Interfaces;
use Illuminate\Support\Facades\Request;

interface QuestionnaireRepositoryInterface
{
    public function load();
    public function store($data);
    public function disagree(int $id,int $hid);
    public function agree(int $id,int $hid);

    public function uploadHeadshots($data,$ids);


    public function primaryHeadshot($data);
    public function update();
    public function delete();
}
