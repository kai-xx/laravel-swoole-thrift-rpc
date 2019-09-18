<?php


namespace App\Library;


class Kernel
{
    private $impl = [
        \App\Library\OrderServiceImpl::class,
        \App\Library\UserServiceImpl::class
    ];

    public function getImpl(){
        return $this->impl;
    }
}
