<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRejectedReservationRequest;
use App\Http\Requests\UpdateRejectedReservationRequest;
use App\Models\RejectedReservation;

class RejectedReservationController extends Controller
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
     * @param  \App\Http\Requests\StoreRejectedReservationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRejectedReservationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RejectedReservation  $rejectedReservation
     * @return \Illuminate\Http\Response
     */
    public function show(RejectedReservation $rejectedReservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RejectedReservation  $rejectedReservation
     * @return \Illuminate\Http\Response
     */
    public function edit(RejectedReservation $rejectedReservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRejectedReservationRequest  $request
     * @param  \App\Models\RejectedReservation  $rejectedReservation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRejectedReservationRequest $request, RejectedReservation $rejectedReservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RejectedReservation  $rejectedReservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(RejectedReservation $rejectedReservation)
    {
        //
    }
}
