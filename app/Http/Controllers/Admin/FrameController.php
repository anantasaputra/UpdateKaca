<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FrameController extends Controller
{
    // HAPUS middleware() dari constructor - Laravel 11+ tidak support

    /**
     * Display a listing of frames
     */
    public function index()
    {
        $frames = Frame::with('category')
            ->latest()
            ->paginate(20);

        return view('admin.frames.index', compact('frames'));
    }

    /**
     * Show the form for creating a new frame
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.create', compact('categories'));
    }

    /**
     * Store a newly created frame
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'image_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('frames', 'public');
            $validated['image_path'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        Frame::create($validated);

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the frame
     */
    public function edit(Frame $frame)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.edit', compact('frame', 'categories'));
    }

    /**
     * Update the specified frame
     */
    public function update(Request $request, Frame $frame)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_path')) {
            // Delete old image
            if ($frame->image_path) {
                Storage::disk('public')->delete($frame->image_path);
            }
            
            $path = $request->file('image_path')->store('frames', 'public');
            $validated['image_path'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');

        $frame->update($validated);

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame berhasil diupdate!');
    }

    /**
     * Remove the specified frame
     */
    public function destroy(Frame $frame)
    {
        if ($frame->image_path) {
            Storage::disk('public')->delete($frame->image_path);
        }

        $frame->delete();

        return redirect()->route('admin.frames.index')
            ->with('success', 'Frame berhasil dihapus!');
    }

    /**
     * Toggle frame active status
     */
    public function toggle(Frame $frame)
    {
        $frame->update(['is_active' => !$frame->is_active]);

        $status = $frame->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Frame {$frame->name} berhasil {$status}.");
    }
}
