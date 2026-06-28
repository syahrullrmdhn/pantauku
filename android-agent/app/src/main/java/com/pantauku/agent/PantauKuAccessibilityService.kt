package com.pantauku.agent

import android.accessibilityservice.AccessibilityService
import android.accessibilityservice.AccessibilityServiceInfo
import android.content.pm.ApplicationInfo
import android.util.Log
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import java.text.SimpleDateFormat
import java.util.*

class PantauKuAccessibilityService : AccessibilityService() {
    companion object {
        const val TAG = "PantauKuAS"

        // Package ignore list — system apps, launchers, keyboards, etc.
        val IGNORED_PACKAGES = setOf(
            // Android System
            "android",
            "com.android.systemui",
            "com.android.settings",
            "com.android.phone",
            "com.android.dialer",
            "com.android.contacts",
            "com.android.mms",
            "com.android.calendar",
            "com.android.calculator2",
            "com.android.deskclock",
            "com.android.gallery3d",
            "com.android.documentsui",
            "com.android.packageinstaller",
            "com.android.permissioncontroller",
            "com.android.providers.downloads",
            "com.android.providers.media",
            "com.android.shell",
            "com.android.nfc",
            "com.android.bluetooth",
            "com.android.keychain",
            "com.android.camera2",
            "com.android.wallpaper",
            "com.android.vending", // Play Store
            // Google System
            "com.google.android.gms",
            "com.google.android.gsf",
            "com.google.android.apps.maps",
            "com.google.android.apps.photos",
            "com.google.android.apps.docs",
            "com.google.android.apps.messaging",
            "com.google.android.apps.wellbeing",
            "com.google.android.apps.setupwizard",
            "com.google.android.apps.restore",
            "com.google.android.syncadapters",
            "com.google.android.backuptransport",
            "com.google.android.partnersetup",
            "com.google.android.gms.policy_sidecar",
            "com.google.android.ext.services",
            "com.google.android.ext.shared",
            "com.google.android.as",
            "com.google.android.settings.intelligence",
            "com.google.android.cellbroadcastservice",
            // Samsung System
            "com.samsung.android",
            "com.sec.android",
            "com.samsung.android.app",
            // Xiaomi System
            "com.miui",
            "com.xiaomi",
            // Oppo System
            "com.oppo",
            "com.coloros",
            // Huawei System
            "com.huawei",
            // OnePlus System
            "com.oneplus",
            // Launchers
            "com.google.android.apps.nexuslauncher",
            "com.android.launcher3",
            "com.sec.android.app.launcher",
            "com.miui.home",
            "com.oppo.launcher",
            "com.huawei.android.launcher",
            // Keyboards
            "com.google.android.inputmethod.latin",
            "com.samsung.android.honeyboard",
            "com.touchtype.swiftkey",
            // This app itself
            "com.pantauku.agent"
        )

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
                
                // Skip ignored/system packages
                if (isIgnoredPackage(packageName)) return
                if (isSystemPackage(packageName)) return
                
                if (packageName != lastPackageName) {
                    lastPackageName = packageName
                    EventQueue.enqueue(
                        EventData(
                            type = "app_open",
                            value = packageName,
                            deviceId = PrefsManager.getDeviceId(this),
                            deviceName = PrefsManager.getDeviceName(this)
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

    private fun isIgnoredPackage(packageName: String): Boolean {
        // Direct match
        if (IGNORED_PACKAGES.contains(packageName)) return true
        // Prefix match for vendor system apps (com.android.*, com.google.*, etc.)
        for (prefix in listOf(
            "com.android.",
            "com.google.android.",
            "com.samsung.android.",
            "com.sec.android.",
            "com.miui.",
            "com.xiaomi.",
            "com.oppo.",
            "com.coloros.",
            "com.huawei.",
            "com.oneplus."
        )) {
            if (packageName.startsWith(prefix)) return true
        }
        return false
    }

    private fun isSystemPackage(packageName: String): Boolean {
        return try {
            val appInfo: ApplicationInfo = packageManager.getApplicationInfo(packageName, 0)
            (appInfo.flags and ApplicationInfo.FLAG_SYSTEM) != 0
        } catch (e: Exception) {
            // If can't determine, assume user app
            false
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
                        deviceId = PrefsManager.getDeviceId(this),
                        deviceName = PrefsManager.getDeviceName(this)
                    )
                )
                Log.d(TAG, "URL detected: $domain")
            }
        }
    }

    private fun findUrlInNode(node: AccessibilityNodeInfo): String? {
        if (node.className?.toString()?.contains("EditText") == true) {
            val text = node.text?.toString()
            if (text != null && (text.startsWith("http") || text.contains("."))) {
                return text
            }
        }
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
