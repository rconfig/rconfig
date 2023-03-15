<?php

namespace App\Models;

class Banner extends Setting
{
    // sub model specifically to return banner from DB for guest on login page
    public function getBanner()
    {
        return parent::select('login_banner')->where('id', 1)->get();
    }
}
