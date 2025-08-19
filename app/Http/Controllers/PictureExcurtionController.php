<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePictureExcurtionRequest;
use App\Http\Requests\UpdatePictureExcurtionRequest;
use App\Models\Excurtion;
use App\Models\PictureExcurtion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PictureExcurtionController extends Controller
{
    public function manage(Request $request)
    {
        $result = [
            'created' => [],
            'updated' => [],
            'deleted' => []
        ];

        // 🟢 CREAR
        if ($request->has('create')) {
            foreach ($request->create as $fileData) {
                $validated = validator($fileData, [
                    'file' => 'required',
                    'order' => 'nullable',
                    'excurtion_id' => 'required',
                    'type' => 'nullable|string',
                ])->validate();

                switch ($fileData['type']) {
                    case 'vid':
                        $url_path = $fileData['file'];
                        break;
                    case 'logo':
                        $path = $this->saveImage($fileData['file'], "excursions/logos");
                        $url_path = null;
                        $excurtion = Excurtion::find($fileData['excurtion_id']); 
                        $excurtion->icon = substr($path, 1);
                        $excurtion->save();

                        return response()->json([
                            'status' => 'success',
                            'excurtion' => $excurtion
                        ]);
                        break;
                    
                    default:
                        $path = $this->saveImage($fileData['file']);
                        $url_path = url($path);
                        break;
                }
                
                if($url_path){
                    $created = PictureExcurtion::create([
                        'link' => $url_path,
                        'order' => $fileData['order'] ?? 0,
                        'excurtion_id' => $fileData['excurtion_id'],
                        'type' => $fileData['type'] ?? 'pic',
                    ]);
                    
                    $result['created'][] = $created;
                }
            }
        }

        // 🟡 ACTUALIZAR
        if ($request->has('update')) {
            foreach ($request->update as $updateData) {
                $picture = PictureExcurtion::findOrFail($updateData['id']);
                
                if(isset($updateData['order'])){
                    $picture->order = $updateData['order'];
                }

                if(isset($updateData['file'])){
                    unlink(public_path(parse_url($picture->link, PHP_URL_PATH)));
                    $path = $this->saveImage($updateData['file']);
                    $picture->link = url($path);
                }

                $picture->save();

                $result['updated'][] = $picture;

            }
        }

        // 🔴 ELIMINAR
        if ($request->has('delete_ids')) {
            $ids = $request->delete_ids ?? null;
            if($ids){
                $pictures_excurtions = PictureExcurtion::whereIn('id', $ids)->get();
                if(isset($pictures_excurtions)){
                    foreach($pictures_excurtions as $picture_excurtion) {
                        unlink(public_path(parse_url($picture_excurtion->link, PHP_URL_PATH)));
                        $picture_excurtion->delete();
                        $result['deleted'][] = $picture_excurtion->id;
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'result' => $result
        ]);
    }

    private function saveImage($file, $url_path = null)
    {
        $url_base = $url_path ?? "store/pictureExcurtion";

        $fileName = Str::random(5) . time() . '.' . $file->extension();
        $file->move(public_path("$url_base"), $fileName);
        $path = "/$url_base/$fileName";
        return $path; // URL pública
    }

    public function getByExcurtion($excurtion_id)
    {
        $pictures = PictureExcurtion::where('excurtion_id', $excurtion_id)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pictures
        ]);
    }


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
     * @param  \App\Http\Requests\StorePictureExcurtionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePictureExcurtionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PictureExcurtion  $pictureExcurtion
     * @return \Illuminate\Http\Response
     */
    public function show(PictureExcurtion $pictureExcurtion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PictureExcurtion  $pictureExcurtion
     * @return \Illuminate\Http\Response
     */
    public function edit(PictureExcurtion $pictureExcurtion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePictureExcurtionRequest  $request
     * @param  \App\Models\PictureExcurtion  $pictureExcurtion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePictureExcurtionRequest $request, PictureExcurtion $pictureExcurtion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PictureExcurtion  $pictureExcurtion
     * @return \Illuminate\Http\Response
     */
    public function destroy(PictureExcurtion $pictureExcurtion)
    {
        //
    }
}
