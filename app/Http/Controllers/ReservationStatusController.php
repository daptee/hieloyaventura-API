<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationStatusRequest;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Models\ReservationStatus;

class ReservationStatusController extends Controller
{
    public function index()
    {
        $reservationStatus = ReservationStatus::pluck('id', 'name');

        return response()->json($reservationStatus);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReservationStatusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReservationStatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function show(ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReservationStatusRequest  $request
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReservationStatusRequest $request, ReservationStatus $reservationStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReservationStatus  $reservationStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReservationStatus $reservationStatus)
    {
        //
    }
}
