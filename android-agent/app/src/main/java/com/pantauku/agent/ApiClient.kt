package com.pantauku.agent

import android.util.Log
import kotlinx.coroutines.*
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.IOException

object ApiClient {
    private const val TAG = "PantauKuAPI"
    private val JSON = "application/json; charset=utf-8".toMediaType()
    private val client = OkHttpClient.Builder()
        .connectTimeout(30, java.util.concurrent.TimeUnit.SECONDS)
        .readTimeout(30, java.util.concurrent.TimeUnit.SECONDS)
        .writeTimeout(30, java.util.concurrent.TimeUnit.SECONDS)
        .build()
    private val scope = CoroutineScope(Dispatchers.IO + SupervisorJob())

    fun sendEvents(events: List<EventData>, url: String, token: String) {
        if (events.isEmpty()) return
        scope.launch {
            try {
                val json = buildJsonArray(events)
                val body = json.toRequestBody(JSON)
                val request = Request.Builder()
                    .url(url)
                    .addHeader("Authorization", "Bearer " + token)
                    .addHeader("Content-Type", "application/json")
                    .post(body)
                    .build()
                val response = client.newCall(request).execute()
                if (response.isSuccessful) {
                    Log.d(TAG, "Sent ${events.size} events OK")
                } else {
                    Log.e(TAG, "Server error: ${response.code}")
                    // Re-queue for retry
                    EventQueue.requeue(events)
                }
                response.close()
            } catch (e: IOException) {
                Log.e(TAG, "Network error: ${e.message}")
                EventQueue.requeue(events)
            } catch (e: Exception) {
                Log.e(TAG, "Error: ${e.message}")
                EventQueue.requeue(events)
            }
        }
    }

    private fun buildJsonArray(events: List<EventData>): String {
        val sb = StringBuilder("[")
        events.forEachIndexed { i, event ->
            if (i > 0) sb.append(",")
            sb.append("""{"type":"${event.type}","value":"${escapeJson(event.value)}","device_id":"${event.deviceId}""")
            if (event.deviceName.isNotEmpty()) {
                sb.append(""","device_name":"${escapeJson(event.deviceName)}"""")
            }
            if (event.latitude != null && event.longitude != null) {
                sb.append(""","latitude":${event.latitude},"longitude":${event.longitude}""")
            }
            sb.append(""","occurred_at":"${event.occurredAt}"}""")
        }
        sb.append("]")
        return sb.toString()
    }

    private fun escapeJson(s: String): String {
        return s.replace("\\", "\\\\")
            .replace("\"", "\\\"")
            .replace("\n", "\\n")
            .replace("\r", "\\r")
            .replace("\t", "\\t")
    }
}
