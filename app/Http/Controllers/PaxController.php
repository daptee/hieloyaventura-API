<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaxRequest;
use App\Http\Requests\UpdatePaxRequest;
use App\Models\Pax;

class PaxController extends Controller
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
     * @param  \App\Http\Requests\StorePaxRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaxRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function show(Pax $pax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function edit(Pax $pax)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaxRequest  $request
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaxRequest $request, Pax $pax)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pax  $pax
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pax $pax)
    {
        //
    }
}
