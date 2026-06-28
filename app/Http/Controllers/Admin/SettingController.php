<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show settings form.
     */
    public function index()
    {
        $telegramBotToken = Setting::get('telegram_bot_token', '');
        $telegramChatId = Setting::get('telegram_chat_id', '');

        return view('admin.settings', compact('telegramBotToken', 'telegramChatId'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:100',
        ]);

        Setting::set('telegram_bot_token', $validated['telegram_bot_token'] ?? '');
        Setting::set('telegram_chat_id', $validated['telegram_chat_id'] ?? '');

        return redirect('/settings')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
