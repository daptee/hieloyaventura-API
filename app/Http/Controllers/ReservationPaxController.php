<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationPaxRequest;
use App\Http\Requests\UpdateReservationPaxRequest;
use App\Models\ReservationPax;

class ReservationPaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreReservationPaxRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReservationPaxRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReservationPax  $reservationPax
     * @return \Illuminate\Http\Response
     */
    public function show(ReservationPax $reservationPax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReservationPax  $reservationPax
     * @return \Illuminate\Http\Response
     */
    public function edit(ReservationPax $reservationPax)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReservationPaxRequest  $request
     * @param  \App\Models\ReservationPax  $reservationPax
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReservationPaxRequest $request, ReservationPax $reservationPax)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReservationPax  $reservationPax
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReservationPax $reservationPax)
    {
        //
    }
}
