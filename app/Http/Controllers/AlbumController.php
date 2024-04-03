<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlbumRequest;
use App\Models\Album;
use App\Models\Image;
use App\Traits\UploadImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    use UploadImages;
    public function index()
    {
        $albums = Album::all();
        return view('albums.index', compact('albums'));
    }

    public function create()
    {
        return view('albums.create');
    }


    public function store(AlbumRequest $request)        //create album in db only and will create it on server when have images
    {
        $request->validated();
        Album::create([
            'name' => $request->name
        ]);
        flash()->addsuccess(trans('messages.save_message'));
        return back();
    }


    public function edit($id)
    {
        $album = Album::findOrFail($id);
        return view('albums.edit', compact('album'));
    }

    public function update(AlbumRequest $request, $id)     //update album only without images based on task info.
    {
        $request->validated();
        $album = Album::find($id);
        $album->update([
            'name' => $request->name
        ]);

        flash()->addsuccess(trans('messages.save_message'));
        return back();
    }


    public function destroy($id)       //delete album that doesnt have any images
    {
        $album = Album::findOrFail($id);
        $album->delete();
        //check if album exist on server
        $exists = Storage::disk('upload_images')->exists($album->uuid);
        if ($exists) {
            Storage::disk('upload_images')->deleteDirectory($album->uuid);
        }
        flash()->addError(trans('messages.delete_message'));
        return back();
    }

    public function createImages($id)
    {
        $album = Album::findOrFail($id);
        return view('albums.create_images', compact('album'));
    }

    public function uploadImages(Request $request, $id)
    {         //upload images on server

        $request->validate([
            'dzfile' => 'required|image',
        ]);
        $album = Album::findOrFail($id);
        $file = $request->file('dzfile');
        $image_name = $file->getclientoriginalname();
        $file_name = $file->storeAs($album->uuid, $image_name, 'upload_images');
        return response()->json([
            'name' => $file_name,
            'original_name' => $file->getClientOriginalName(),    //append filename to use it for save images in db
        ]);
    }
    public function storeImages(Request $request, $id)        //store images in db using polymorph relationship
    {

        $request->validate([
            'document' => 'required|array',
            'document.*' => 'required|max:2048',

        ]);

        $album = Album::findOrFail($id);

        if ($request->has('document') && count($request->document) > 0) {
            foreach ($request->document as $image) {
                Image::create([
                    'name' => $image,
                    'imageable_id' => $album->id,
                    'imageable_type' => Album::class,
                ]);
            }
        }

        flash()->addSuccess(trans('messages.save_message'));
        return back();
    }
    public function destroyOrMove(Request $request, $id)
    {

        $request->validate([
            'submit' => 'required|in:delete,move', 
        ]);
        $album = Album::findOrFail($id);

        if ($request->submit == 'delete') {
            $album->images()->delete();
            // Check if the album directory exists on the server
            if (Storage::disk('upload_images')->exists($album->uuid)) {
                Storage::disk('upload_images')->deleteDirectory($album->uuid);
            }
            $album->delete();
            flash()->addError(trans('messages.delete_message'));
            return back();
        }

        if ($request->submit == 'move') {
            $albums=Album::where('id','!=',$id)->get();
            return view('albums.move_to_folder', compact('id','albums'));
        }
    }


    public function moveToFolder(Request $request, $id)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id|integer'
        ]);

        $oldAlbum = Album::findOrFail($id);
        $newAlbumId = $request->input('album_id');
        $newAlbum = Album::findOrFail($newAlbumId);

        if (!Storage::disk('upload_images')->exists($newAlbum->uuid)) {
            Storage::disk('upload_images')->makeDirectory($newAlbum->uuid);
        }

        $images = $oldAlbum->images;
        foreach ($images as $image) {
            $oldPath = $image->name;
            $newPath = $newAlbum->uuid . '/' . $image->name;
            Storage::disk('upload_images')->move($oldPath, $newPath);
            $image->update(['imageable_id' => $newAlbumId]);
        }

        if (Storage::disk('upload_images')->exists($oldAlbum->uuid)) {
            Storage::disk('upload_images')->deleteDirectory($oldAlbum->uuid);
        }

        $oldAlbum->delete();

        flash()->addSuccess(trans('messages.move_message'));
        return redirect(route('albums.index'));
    }
    }
