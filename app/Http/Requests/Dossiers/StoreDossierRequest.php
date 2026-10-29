<?php

namespace App\Http\Requests\Dossiers;

use App\Models\DegreeJuridiction;
use App\Rules\NumeroDossierValide;
use App\Support\NumeroDossier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation des données pour la création d'un dossier judiciaire.
 */
class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Le dossier naît avec son instance de première instance : le numéro saisi
     * est celui de cette juridiction (format standard année/code/ordre).
     */
    protected function prepareForValidation(): void
    {
        NumeroDossier::fusionner($this, NumeroDossier::STANDARD);
    }

    public function rules(): array
    {
        $premierDegre = DegreeJuridiction::premierDegre();

        return [
            'id_tribunal' => [
                'required',
                Rule::exists('tribunaux', 'id')->when($premierDegre, fn ($r) => $r->where('id_degre', $premierDegre->id)),
            ],
            'id_commune'              => ['nullable', 'exists:communes,id'],
            'numero_dossier_tribunal' => [
                'nullable',
                'string',
                new NumeroDossierValide(NumeroDossier::STANDARD, (int) $this->input('id_tribunal') ?: null),
            ],
            'id_type_affaire'         => 'required|exists:type_affaires,id',
            'date_ouverture'          => 'required|date',
            'date_cloture'            => 'nullable|date|after:date_ouverture',
            'objet_litige'            => 'nullable|string|max:5000',
            'action_service'          => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            // type affaire
            'id_type_affaire.required'       => 'يرجى اختيار الشعبة.',
            'id_type_affaire.exists'         => 'الشعبة غير صالحة.',

            // dates
            'date_ouverture.required'        => 'تاريخ فتح الملف مطلوب.',
            'date_ouverture.date'            => 'تاريخ فتح الملف غير صالح.',

            'date_cloture.date'              => 'تاريخ الإغلاق غير صالح.',
            'date_cloture.after'             => 'يجب أن يكون تاريخ الإغلاق بعد تاريخ الفتح.',
            'id_tribunal.required'           => 'يرجى اختيار المحكمة الابتدائية.',
            'id_tribunal.exists'             => 'المحكمة غير صالحة: يجب اختيار محكمة من الدرجة الأولى.',
            'id_commune.exists'              => 'الجماعة الترابية غير صالحة.',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_dossier_tribunal' => 'رقم الملف بالمحكمة',
            'id_tribunal' => 'المحكمة',
            'id_commune' => 'الجماعة الترابية',
            'id_type_affaire' => 'الشعبة',
            'objet_litige' => 'موضوع النزاع',
            'action_service' => 'الإجراء المتخذ من طرف المصلحة',
            'date_ouverture' => 'تاريخ فتح الملف',
            'date_cloture' => 'تاريخ الإغلاق',
        ];
    }
}