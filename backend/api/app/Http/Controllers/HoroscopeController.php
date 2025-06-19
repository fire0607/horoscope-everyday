<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horoscope;

class HoroscopeController extends Controller
{
    public function index()
    {
        $horoscopes = Horoscope::orderBy('date', 'desc')->get();
        return response()->json($horoscopes);
    }
}
