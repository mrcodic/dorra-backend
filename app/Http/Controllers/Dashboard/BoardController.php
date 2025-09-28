<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;




class BoardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard.board',get_defined_vars());
    }
}
