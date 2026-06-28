package com.pantauku.agent

import android.content.Context
import android.util.Log
import kotlinx.coroutines.*

object EventQueue {
    private const val TAG = "PantauKuQueue"
    private const val MAX_QUEUE = 500
    private const val FLUSH_INTERVAL_MS = 5000L
    private const val MAX_RETRIES = 10

    private val queue = mutableListOf<EventData>()
    private val retryQueue = mutableListOf<EventData>()
    private var retryCount = 0
    private var isFlushing = false

    private var job: Job? = null
    private var context: Context? = null

    fun init(ctx: Context) {
        context = ctx.applicationContext
        startPeriodicFlush()
        Log.d(TAG, "EventQueue initialized")
    }

    @Synchronized
    fun enqueue(event: EventData) {
        if (queue.size >= MAX_QUEUE) {
            queue.removeAt(0)
        }
        queue.add(event)
        Log.d(TAG, "Enqueued: ${event.type}=${event.value} (size=${queue.size})")
    }

    @Synchronized
    fun requeue(events: List<EventData>) {
        retryCount++
        if (retryCount > MAX_RETRIES) {
            Log.e(TAG, "Max retries exceeded, dropping ${events.size} events")
            retryCount = 0
            retryQueue.clear()
            return
        }
        retryQueue.addAll(events)
        if (retryQueue.size > MAX_QUEUE) {
            retryQueue.subList(0, retryQueue.size - MAX_QUEUE).clear()
        }
    }

    @Synchronized
    private fun drainQueue(): List<EventData> {
        val events = mutableListOf<EventData>()
        events.addAll(queue)
        events.addAll(retryQueue)
        queue.clear()
        retryQueue.clear()
        retryCount = 0
        return events
    }

    private fun startPeriodicFlush() {
        val ctx = context ?: return
        job = CoroutineScope(Dispatchers.IO).launch {
            while (isActive) {
                delay(FLUSH_INTERVAL_MS)
                flush(ctx)
            }
        }
    }

    @Synchronized
    fun flush(ctx: Context) {
        val events = drainQueue()
        if (events.isEmpty()) return
        if (isFlushing) {
            // Re-add events if already flushing
            retryQueue.addAll(0, events)
            return
        }
        isFlushing = true
        val url = PrefsManager.getApiUrl(ctx)
        val token = PrefsManager.getApiToken(ctx)
        ApiClient.sendEvents(events, url, token)
        isFlushing = false
    }

    fun stop() {
        job?.cancel()
    }
}
