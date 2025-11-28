<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Settings\GeneralSettings;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Show settings page
     *
     * @param GeneralSettings $settings
     * 
     * @return View
     */
    public function index(GeneralSettings $settings)
    {
        return view('settings.edit', compact('settings'));
    }

    /**
     * Update settings
     * 
     * @param GeneralSettings $settings
     * @param UpdateGeneralSettingsRequest $request
     * 
     * @return RedirectResponse
     */
    public function update(GeneralSettings $settings, UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $settings->fill($validated)->save();

        return redirect()->route('settings.show');
    }
}
