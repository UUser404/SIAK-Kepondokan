<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class EkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        $query = Ekstrakurikuler::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        $ekstrakurikuler = $query->orderBy('nama')->paginate(20)->withQueryString();

        return view('ekstrakurikuler.index', compact('ekstrakurikuler'));
    }

    public function create()
    {
        return view('ekstrakurikuler.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:100', 'unique:ekstrakurikuler,nama'],
            'pembina' => ['nullable', 'string', 'max:100'],
        ]);

        $ekskul = Ekstrakurikuler::create(array_merge($validated, ['is_active' => true]));

        ActivityLogService::logCreate($ekskul);

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function show(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('ekstrakurikuler.show', compact('ekstrakurikuler'));
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('ekstrakurikuler.edit', compact('ekstrakurikuler'));
    }

    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'nama'    => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('ekstrakurikuler', 'nama')->ignore($ekstrakurikuler->id),
            ],
            'pembina' => ['nullable', 'string', 'max:100'],
        ]);

        $old = $ekstrakurikuler->toArray();
        $ekstrakurikuler->update($validated);

        ActivityLogService::logUpdate($ekstrakurikuler, $old);

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->update(['is_active' => false]);
        ActivityLogService::logDelete($ekstrakurikuler);

        return back()->with('success', 'Ekstrakurikuler dinonaktifkan.');
    }
}
