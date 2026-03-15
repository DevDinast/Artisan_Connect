<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function show($id) {
    $artisan = Artisan::findOrFail($id);
    return view('artisan.profile', compact('artisan'));
}

}


