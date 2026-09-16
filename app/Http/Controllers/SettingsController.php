<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePreferencesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The account's own preferences. They used to be stored once for the whole
 * instance; every account keeps its own now.
 */
class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index(Request $request): View
    {
        return view('settings.edit', ['user' => $request->user()]);
    }

    /**
     * Update the settings of the signed in account.
     */
    public function update(UpdatePreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->dark = $request->validated()['dark'];
        $user->save();

        return redirect()->route('settings.show')->with('status', 'Settings saved.');
    }
}
