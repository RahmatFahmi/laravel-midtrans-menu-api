<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Http\Requests\Admin\MenuItemRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $menus = MenuItem::with('category')
            ->search($request->search)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.menu_items.index', compact('menus'));
    }

    public function show(MenuItem $menu)
    {
        // Eager load category untuk performa
        $menu->load('category');
        return view('admin.menu_items.show', compact('menu'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.menu_items.create', compact('categories'));
    }

    public function store(MenuItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        MenuItem::create($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu ditambahkan!');
    }

    public function edit(MenuItem $menu)
    {
        $categories = Category::all();
        return view('admin.menu_items.edit', compact('menu', 'categories'));
    }

    public function update(MenuItemRequest $request, MenuItem $menu)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($menu->image) Storage::disk('public')->delete($menu->image);
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        $menu->update($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu diperbarui!');
    }

    public function destroy(MenuItem $menu)
    {
        if ($menu->image) Storage::disk('public')->delete($menu->image);
        $menu->delete();
        return back()->with('success', 'Menu dihapus!');
    }

    private function uploadImage($file)
    {
        $filename = time() . '.webp';
        $img = Image::read($file->getRealPath());
        $img->scale(width: 800); // Resize untuk optimalisasi Android Compose
        $webp = $img->toWebp(80);

        Storage::disk('public')->put('menus/' . $filename, $webp);
        return 'menus/' . $filename;
    }
}
