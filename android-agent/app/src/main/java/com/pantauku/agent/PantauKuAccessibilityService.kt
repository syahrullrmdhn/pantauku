package com.pantauku.agent

import android.accessibilityservice.AccessibilityService
import android.accessibilityservice.AccessibilityServiceInfo
import android.util.Log
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import java.text.SimpleDateFormat
import java.util.*

class PantauKuAccessibilityService : AccessibilityService() {
    companion object {
        const val TAG = "PantauKuAS"
        val BROWSER_PACKAGES = setOf(
            "com.android.chrome",
            "com.chrome.beta",
            "com.chrome.dev",
            "org.mozilla.firefox",
            "com.brave.browser",
            "com.opera.browser",
            "com.opera.mini.native",
            "com.kiwibrowser.browser",
            "com.microsoft.emmx",
            "com.uc.browser.en",
            "com.UCMobile.intl"
        )
    }

    private var lastPackageName = ""
    private var lastUrl = ""
    private var lastUrlTime = 0L

    override fun onServiceConnected() {
        super.onServiceConnected()
        val info = AccessibilityServiceInfo().apply {
            eventTypes = AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED or
                    AccessibilityEvent.TYPE_VIEW_FOCUSED or
                    AccessibilityEvent.TYPE_VIEW_TEXT_CHANGED
            feedbackType = AccessibilityServiceInfo.FEEDBACK_GENERIC
            flags = AccessibilityServiceInfo.FLAG_REPORT_VIEW_IDS or
                    AccessibilityServiceInfo.FLAG_RETRIEVE_INTERACTIVE_WINDOWS
            notificationTimeout = 100
        }
        serviceInfo = info
        Log.d(TAG, "Accessibility service connected")
    }

    override fun onAccessibilityEvent(event: AccessibilityEvent?) {
        if (event == null) return

        when (event.eventType) {
            AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED -> {
                val packageName = event.packageName?.toString() ?: return
                if (packageName != lastPackageName) {
                    lastPackageName = packageName
                    EventQueue.enqueue(
                        EventData(
                            type = "app_open",
                            value = packageName,
                            deviceId = PrefsManager.getDeviceId(this)
                        )
                    )
                    Log.d(TAG, "App opened: $packageName")
                }
            }
            AccessibilityEvent.TYPE_VIEW_TEXT_CHANGED,
            AccessibilityEvent.TYPE_VIEW_FOCUSED -> {
                if (BROWSER_PACKAGES.contains(lastPackageName)) {
                    extractUrlFromBrowser()
                }
            }
        }
    }

    private fun extractUrlFromBrowser() {
        val root = rootInActiveWindow ?: return
        val url = findUrlInNode(root)
        if (url != null && url != lastUrl) {
            val now = System.currentTimeMillis()
            if (now - lastUrlTime > 3000) {
                val domain = extractDomain(url)
                lastUrl = url
                lastUrlTime = now
                EventQueue.enqueue(
                    EventData(
                        type = "browser_access",
                        value = domain,
                        deviceId = PrefsManager.getDeviceId(this)
                    )
                )
                Log.d(TAG, "URL detected: $domain")
            }
        }
    }

    private fun findUrlInNode(node: AccessibilityNodeInfo): String? {
        // Check if this node is an address bar
        if (node.className?.toString()?.contains("EditText") == true) {
            val text = node.text?.toString()
            if (text != null && (text.startsWith("http") || text.contains("."))) {
                return text
            }
        }
        // Recurse children
        for (i in 0 until node.childCount) {
            val child = node.getChild(i) ?: continue
            val result = findUrlInNode(child)
            if (result != null) return result
        }
        return null
    }

    private fun extractDomain(url: String): String {
        return try {
            val clean = if (url.contains("://")) {
                url.substring(url.indexOf("://") + 3)
            } else url
            val domain = clean.substringBefore("/").substringBefore("?")
            if (domain.startsWith("www.")) domain.substring(4) else domain
        } catch (e: Exception) {
            url
        }
    }

    override fun onInterrupt() {
        Log.d(TAG, "Accessibility service interrupted")
    }
}
