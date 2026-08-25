<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('stores', 'creator');

        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $query->whereHas('stores', fn($q) => $q->where('stores.id', auth()->user()->store_id));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $events = $query->latest()->paginate(15)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : collect();

        return view('admin.events.form', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'icon_svg' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'is_active' => $request->boolean('is_active'),
            'icon_svg' => $request->icon_svg,
            'color' => $request->color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => auth()->id(),
        ]);

        $this->syncStores($event, $request);

        $route = request()->is('store*') ? 'storepanel_cat.events.index' : 'admin.events.index';
        return redirect()->route($route)
            ->with('success', 'Event created!');
    }

    public function show(Event $event)
    {
        $event->load('stores');

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $event->load('stores');
        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : collect();

        return view('admin.events.form', compact('event', 'stores'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'icon_svg' => 'nullable|string|max:10000',
            'color' => 'nullable|string|max:7',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $event->update([
            'title' => $request->title,
            'is_active' => $request->boolean('is_active'),
            'icon_svg' => $request->icon_svg,
            'color' => $request->color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        $this->syncStores($event, $request);

        $route = request()->is('store*') ? 'storepanel_cat.events.index' : 'admin.events.index';
        return redirect()->route($route)
            ->with('success', 'Event updated!');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        $route = request()->is('store*') ? 'storepanel_cat.events.index' : 'admin.events.index';
        return redirect()->route($route)
            ->with('success', 'Event deleted.');
    }

    private function syncStores(Event $event, Request $request): void
    {
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $event->stores()->sync([auth()->user()->store_id]);
        } else {
            $event->stores()->sync($request->input('stores', []));
        }
    }
}
