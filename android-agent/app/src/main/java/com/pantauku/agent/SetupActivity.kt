package com.pantauku.agent

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.Settings
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class SetupActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_setup)

        findViewById<Button>(R.id.btnAccessibility).setOnClickListener {
            startActivity(Intent(Settings.ACTION_ACCESSIBILITY_SETTINGS))
            Toast.makeText(this, "Cari \"System Service\" lalu aktifkan", Toast.LENGTH_LONG).show()
        }

        findViewById<Button>(R.id.btnBattery).setOnClickListener {
            val intent = Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS).apply {
                data = Uri.parse("package:$packageName")
            }
            try {
                startActivity(intent)
            } catch (e: Exception) {
                // Fallback: open battery settings
                startActivity(Intent(Settings.ACTION_BATTERY_SAVER_SETTINGS))
            }
        }

        findViewById<Button>(R.id.btnStart).setOnClickListener {
            // Start the foreground service
            val serviceIntent = Intent(this, PantauKuService::class.java)
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                startForegroundService(serviceIntent)
            } else {
                startService(serviceIntent)
            }
            EventQueue.init(applicationContext)
            Toast.makeText(this, "Layanan berjalan", Toast.LENGTH_SHORT).show()
            finish()
        }

        val statusText = findViewById<TextView>(R.id.tvStatus)
        statusText.text = "PantauKu Agent v1.0\nSiap diaktifkan"
    }
}
