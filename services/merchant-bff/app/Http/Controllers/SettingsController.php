<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): View
    {
        try {
            $settings = $this->settingService->getAll();
            return view('settings.index', compact('settings'));
        } catch (\Exception $e) {
            return view('settings.index', ['settings' => [], 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            $this->settingService->update($request->all());
            return redirect()->route('settings.index')
                ->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}

