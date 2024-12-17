<?php

namespace App\RepositoryInterfaces;

interface SingleContentRepositoryInterface
{
    public function addContent(array $data);
    public function updateContent(array $data);
    public function deleteContent(array $data);
    public function getContent(array $data);
}
