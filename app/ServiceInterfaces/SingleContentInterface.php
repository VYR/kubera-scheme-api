<?php

namespace App\ServiceInterfaces;

use Illuminate\Http\Request;

interface SingleContentInterface
{
    public function addContent(Request $request);
    public function updateContent(Request $request);
    public function deleteContent(Request $request);
    public function getContent(Request $request);
}
