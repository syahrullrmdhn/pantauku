package com.pantauku.agent

import android.content.Context
import android.provider.Settings
import java.util.UUID

object PrefsManager {
    private const val PREFS_NAME = "pantauku_prefs"
    private const val KEY_DEVICE_ID = "device_id"
    private const val KEY_API_URL = "api_url"
    private const val KEY_API_TOKEN = "api_token"

    fun getDeviceId(context: Context): String {
        val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        var id = prefs.getString(KEY_DEVICE_ID, null)
        if (id == null) {
            id = Settings.Secure.getString(
                context.contentResolver,
                Settings.Secure.ANDROID_ID
            ) ?: UUID.randomUUID().toString()
            prefs.edit().putString(KEY_DEVICE_ID, id).apply()
        }
        return id
    }

    fun getApiUrl(context: Context): String {
        return context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .getString(KEY_API_URL, "https://pantauku.srcode.my.id/api/events") ?: "https://pantauku.srcode.my.id/api/events"
    }

    fun getApiToken(context: Context): String {
        return context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .getString(KEY_API_TOKEN, "pantauku_api_token_2026_secure_random_key") ?: "pantauku_api_token_2026_secure_random_key"
    }

    fun setApiConfig(context: Context, url: String, token: String) {
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .putString(KEY_API_URL, url)
            .putString(KEY_API_TOKEN, token)
            .apply()
    }
}
