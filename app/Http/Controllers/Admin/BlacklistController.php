<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlacklistDomain;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    /**
     * Display blacklist domains list.
     */
    public function index()
    {
        $domains = BlacklistDomain::orderBy('created_at', 'desc')->paginate(25);

        return view('admin.blacklist.index', compact('domains'));
    }

    /**
     * Store a new blacklist domain.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:blacklist_domains,domain',
            'notes' => 'nullable|string|max:1000',
        ]);

        BlacklistDomain::create($validated);

        return redirect('/blacklist')->with('success', 'Domain berhasil ditambahkan ke blacklist.');
    }

    /**
     * Update an existing blacklist domain.
     */
    public function update(Request $request, $id)
    {
        $domain = BlacklistDomain::findOrFail($id);

        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:blacklist_domains,domain,' . $id,
            'notes' => 'nullable|string|max:1000',
        ]);

        $domain->update($validated);

        return redirect('/blacklist')->with('success', 'Domain berhasil diperbarui.');
    }

    /**
     * Delete a blacklist domain.
     */
    public function destroy($id)
    {
        $domain = BlacklistDomain::findOrFail($id);
        $domain->delete();

        return redirect('/blacklist')->with('success', 'Domain berhasil dihapus dari blacklist.');
    }
}
