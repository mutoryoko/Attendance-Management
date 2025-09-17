<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestAttendanceController extends Controller
{
    public function index()
    {
        return view('attendance_request');
    }
}
