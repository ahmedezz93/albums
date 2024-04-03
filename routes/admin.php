<?php



use App\Http\Controllers\AlbumController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::get('/', function () {
    return view('albums.index');
});

Route::group(['prefix' => 'albums'], function () {
    Route::get('index', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('create', [AlbumController::class, 'create'])->name('albums.create');
    Route::get('createImages/{id}', [AlbumController::class, 'createImages'])->name('albums.createImages');   // view page to upload images
    Route::post('upload_images/{id}', [AlbumController::class, 'uploadImages'])->name('upload_images');            //upload images

    Route::post('store_images/{id}', [AlbumController::class, 'storeImages'])->name('albums.store_images');

    Route::get('destroy/{id}', [AlbumController::class, 'destroy'])->name('albums.destroy');
    Route::get('edit/{id}', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::post('update/{id}', [AlbumController::class, 'update'])->name('albums.update');

    Route::post('destroy_or_move/{id}', [AlbumController::class, 'destroyOrMove'])->name('albums.destroy_or_move');
    Route::post('move_to_folder/{id}', [AlbumController::class, 'moveToFolder'])->name('albums.move_to_folder');
    Route::post('store', [AlbumController::class, 'store'])->name('albums.store');
});
