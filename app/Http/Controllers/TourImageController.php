<?php

namespace App\Http\Controllers;

use App\Models\TourImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TourImageController extends Controller
{
  

    // ─────────────────────────────────────────
    // Delete a single tour image
    // ─────────────────────────────────────────
    public function destroy($id)
    {
        $image = TourImage::findOrFail($id);

        // Delete the file from storage
        Storage::disk('public')->delete($image->image);

        // Delete the DB record
        $image->delete();

        return back()->with('success', 'Image removed successfully.');
    }
}