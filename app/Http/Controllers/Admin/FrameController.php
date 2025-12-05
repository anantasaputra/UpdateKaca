<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FrameController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $frames = Frame::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.frames.index', compact('frames'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'frame_image' => 'required|image|mimes:png|max:10240', // PNG only, max 10MB
            'is_active'   => 'boolean',
        ]);

        try {
            $file     = $request->file('frame_image');
            $filename = 'frame_' . time() . '_' . Str::random(10) . '.png';

            // Simpan file
            $file->storeAs('frames', $filename, 'public');

            // Buat record frame
            Frame::create([
                'name'        => $request->name,
                'filename'    => $filename,
                'category_id' => $request->category_id,
                'is_active'   => $request->has('is_active'),
                'uploaded_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.frames.index')
                ->with('success', 'Frame created successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Frame $frame)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.edit', compact('frame', 'categories'));
    }

    public function update(Request $request, Frame $frame)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'frame_image' => 'nullable|image|mimes:png|max:10240',
            'is_active'   => 'boolean',
        ]);

        try {
            $data = [
                'name'        => $request->name,
                'category_id' => $request->category_id,
                'is_active'   => $request->has('is_active'),
            ];

            // Handle file upload jika ada gambar baru
            if ($request->hasFile('frame_image')) {
                // Hapus file lama jika ada
                if ($frame->filename && Storage::disk('public')->exists('frames/' . $frame->filename)) {
                    Storage::disk('public')->delete('frames/' . $frame->filename);
                }

                $file     = $request->file('frame_image');
                $filename = 'frame_' . time() . '_' . Str::random(10) . '.png';

                $file->storeAs('frames', $filename, 'public');

                $data['filename'] = $filename;
            }

            $frame->update($data);

            return redirect()
                ->route('admin.frames.index')
                ->with('success', 'Frame updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Frame $frame)
    {
        try {
            // Hapus file fisik jika perlu
            if ($frame->filename && Storage::disk('public')->exists('frames/' . $frame->filename)) {
                Storage::disk('public')->delete('frames/' . $frame->filename);
            }

            $frame->delete();

            return redirect()
                ->route('admin.frames.index')
                ->with('success', 'Frame deleted successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
