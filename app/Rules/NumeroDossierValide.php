<?php

namespace App\Rules;

use App\Models\DossierTribunal;
use App\Support\NumeroDossier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valide le numéro d'une instance selon le format de sa juridiction, et refuse
 * un numéro déjà porté, au même tribunal, par une instance d'un AUTRE dossier
 * (un même dossier peut garder son numéro d'une instance à l'autre :
 * opposition, révision).
 */
class NumeroDossierValide implements ValidationRule
{
    public function __construct(
        private string $format,
        private ?int $idTribunal = null,
        private ?int $idDossier = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! NumeroDossier::estValide((string) $value, $this->format)) {
            $fail($this->format === NumeroDossier::CASSATION
                ? 'صيغة رقم الملف بمحكمة النقض غير صحيحة. يجب أن تكون: السنة / رمز الهيئة (1-13) / رمز الغرفة (1-7) / الرقم (مثال: 2025/1/4/6327).'
                : 'صيغة رقم الملف غير صحيحة. يجب أن تكون: السنة / رمز الفئة / الرقم (مثال: 2024/7120/1994).');

            return;
        }

        if (! $this->idTribunal) {
            return;
        }

        $existe = DossierTribunal::where('id_tribunal', $this->idTribunal)
            ->where('numero_dossier_tribunal', NumeroDossier::normaliser((string) $value))
            ->when($this->idDossier, fn ($q) => $q->where('id_dossier', '!=', $this->idDossier))
            ->exists();

        if ($existe) {
            $fail('رقم الملف موجود بالفعل بهذه المحكمة لملف آخر.');
        }
    }
}
