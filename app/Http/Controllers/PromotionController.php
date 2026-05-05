<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $promotions = Promotion::query()
            ->withCount('orders')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%");
            })
            ->orderBy('code')
            ->get();

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.promotions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromotionRequest $request): RedirectResponse
    {
        Promotion::query()->create($request->validated());

        return redirect()
            ->route('admin.promotions.index')
            ->with('status', 'Promotion created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', [
            'promotion' => $promotion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()
            ->route('admin.promotions.index')
            ->with('status', 'Promotion updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->orders()->exists()) {
            return redirect()
                ->route('admin.promotions.index')
                ->with('error', 'Promotion cannot be deleted because it has linked orders.');
        }

        $promotion->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('status', 'Promotion deleted successfully.');
    }
}
