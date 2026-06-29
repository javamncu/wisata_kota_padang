<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Concierge\ConciergeUsage;
use App\Services\Concierge\GeminiModels;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConciergeController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->boolean('refresh')) {
            GeminiModels::forget();
        }

        $models = GeminiModels::available();

        return view('admin.concierge', [
            'models' => $models,
            'active' => $this->activeModel(),
            'usage' => ConciergeUsage::usedMany($models),
            'caps' => config('concierge.model_daily_caps', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'concierge_model' => ['required', 'string', Rule::in(GeminiModels::available())],
        ]);

        Settings::setMany(['concierge_model' => $request->input('concierge_model')]);

        return back()->with('status', 'Model AI Concierge diperbarui ke '.$request->input('concierge_model').'.');
    }

    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'model' => ['required', 'string', Rule::in(GeminiModels::available())],
        ]);

        $result = GeminiModels::probe($data['model']);
        $result['used'] = ConciergeUsage::used($data['model']);

        return response()->json($result);
    }

    private function activeModel(): string
    {
        $chosen = setting('concierge_model');

        return is_string($chosen) && $chosen !== ''
            ? $chosen
            : config('concierge.gemini.model');
    }
}
