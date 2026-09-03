<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\TypeStructure;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function index()
    {
        $structures = Structure::with(['typeStructure', 'parent'])
            ->whereNull('id_parent')
            ->with('enfants.typeStructure')
            ->orderBy('nom')
            ->get();

        return view('admin.structures.index', compact('structures'));
    }

    public function create()
    {
        $typesStructure = TypeStructure::all();
        $parents        = Structure::orderBy('nom')->get();

        return view('admin.structures.create', compact('typesStructure', 'parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'id_type_structure' => 'required|exists:type_structures,id',
            'id_parent'         => 'nullable|exists:structures,id',
        ], $this->messages());

        Structure::create($validated);

        return redirect()
            ->route('admin.structures.index')
            ->with('success', 'تم إنشاء الهيكل بنجاح.');
    }

    public function show(Structure $structure)
    {
        $structure->load(['typeStructure', 'parent', 'enfants.typeStructure']);

        return view('admin.structures.show', compact('structure'));
    }

    public function edit(Structure $structure)
    {
        $typesStructure = TypeStructure::all();
        $parents        = Structure::where('id', '!=', $structure->id)
            ->orderBy('nom')
            ->get();

        return view('admin.structures.edit', compact(
            'structure',
            'typesStructure',
            'parents'
        ));
    }

    public function update(Request $request, Structure $structure)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'id_type_structure' => 'required|exists:type_structures,id',
            'id_parent'         => 'nullable|exists:structures,id',
        ], $this->messages());

        $structure->update($validated);

        return redirect()
            ->route('admin.structures.index')
            ->with('success', 'تم تحديث الهيكل بنجاح.');
    }

    public function destroy(Structure $structure)
    {
        $structure->delete();

        return redirect()
            ->route('admin.structures.index')
            ->with('success', 'تم حذف الهيكل بنجاح.');
    }

    /**
     * Messages de validation en arabe.
     */
    protected function messages()
    {
        return [
            'nom.required' => 'اسم الهيكل مطلوب.',
            'nom.string'   => 'اسم الهيكل يجب أن يكون نصاً.',
            'nom.max'      => 'اسم الهيكل يجب ألا يتجاوز 255 حرفاً.',

            'id_type_structure.required' => 'نوع الهيكل مطلوب.',
            'id_type_structure.exists'   => 'نوع الهيكل المحدد غير موجود.',

            'id_parent.exists' => 'الهيكل الأب المحدد غير موجود.',
        ];
    }
}