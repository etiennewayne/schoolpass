<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentTrack;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeScannerController extends Controller
{
    //

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('office');
    }


    public function index(){
        return view('office.office-scanner');
    }

    public function getCurrentUser(){
        return Auth::user();
    }

    public function validateQR(Request $req, $qr){

        $authUser = Auth::user();

        $user = User::where('qr_ref', $qr)->first();

        $dateNow = date('Y-m-d');
        $timeNow = date('H:i');


        $data = Appointment::where('appointment_user_id', $user->user_id)
            ->where('app_date', $dateNow)
            ->where('app_time_from', '<=', $timeNow)
            ->where('app_time_to', '>=', $timeNow)
            ->where('is_approved', 1)
            ->first();


        if($data){
            AppointmentTrack::create([
                'appointment_id' => $data->appointment_id,
                'office_id' => $authUser->office_id,
                'time_out' => $req->remark === 'DONE' ? $timeNow : null,
                'remark' => $req->remark
            ]);

            if($req->remark === 'DONE'){
                $data = Appointment::find($data->appointment_id);
                $data->visit_status = 'DONE';
                $data->save();
            }

            return 1;
        }

        return 0;
        //return $exist;
    }



}
