<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function details()
    {
        return view("dashboard.settings.details");
    } 

    public function notifications()
    {
         return view("dashboard.settings.notifications");
    }

     public function payments()
     {
         return view("dashboard.settings.payments");
     }
     
     public function website()
     {
         return view("dashboard.settings.website");
     }
}


